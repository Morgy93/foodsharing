<?php

namespace Foodsharing\Modules\Message;

use Foodsharing\Modules\Core\View;

final class MessageView extends View
{
	public function leftMenu(): string
	{
		return $this->menu([
			['name' => $this->translationHelper->s('new_message'), 'click' => 'msg.compose();return false;']
		]);
	}

	private function peopleChooser($id, $option = [])
	{
		$this->pageHelper->addJs('
// via https://github.com/yairEO/tagify#ajax-whitelist

// The DOM element you wish to replace with Tagify
var input = document.querySelector("input#' . $id . '.tag");

// initialize Tagify on the above input node reference
var tagify = new Tagify(input, {
  editTags: false,
  enforceWhitelist: true,
  whitelist: [],
  dropdown: {
    position: "all",
    enabled: 3 // show suggestions dropdown after how many typed characters
  },
})

// Chainable event listeners
tagify.on("add", onAddTag)
      .on("remove", onRemoveTag)
      .on("input", onInput)
      .on("edit", onTagEdit)
      .on("invalid", onInvalidTag)
      .on("click", onTagClick)
      .on("focus blur", onTagifyFocusBlur)
      .on("dropdown:select", onDropdownSelect)

// tag added callback
function onAddTag(e){
    console.log("onAddTag: ", e.detail)
    console.log("original input value: ", input.value)
    tagify.off("add", onAddTag) // example of removing a custom Tagify event
}

// tag removed callback
function onRemoveTag(e){
    console.log("onRemoveTag:", e.detail, "tagify instance value:", tagify.value)
}
// on character(s) added/removed (user is typing/deleting)
function onInput(e){
  console.log("onInput: ", e.detail)
  var value = e.detail.value
  tagify.settings.whitelist.length = 0 // reset current whitelist
  // https://developer.mozilla.org/en-US/docs/Web/API/AbortController/abort
  // controller && controller.abort()
  // controller = new AbortController()
  tagify.loading(true).dropdown.hide.call(tagify) // show the loader animation

  // get new search results (whitelist) from server
  $.ajax({
    url: "/api/search/user",
    data: {q: value},
    dataType: "json",
    success: function(json){
      // https://stackoverflow.com/q/30640771/104380
      // replace tagify "whitelist" array values with new values (JSON result)
      console.log("RETVAL", value, json)
      tagify.settings.whitelist.splice(0, json.length, ...json)
      // render the suggestions dropdown. "newValue" is when "input" event is called while editing a tag
      tagify.loading(false).dropdown.show.call(tagify, e.detail.value)
    }
  })
}

function onTagEdit(e){
    console.log("onTagEdit: ", e.detail)
}
function onInvalidTag(e){
    console.log("onInvalidTag: ", e.detail)
}

function onTagClick(e){
    console.log(e.detail)
    console.log("onTagClick: ", e.detail)
}

function onTagifyFocusBlur(e){
    console.log(e.type, "event fired")
}

function onDropdownSelect(e){
    console.log("onDropdownSelect: ", e.detail)
}
		');

		$input = '<input type="text" name="' . $id . '" id="' . $id . '" value="" class="tag input text value" />';

		return $this->v_utils->v_input_wrapper($this->translationHelper->s($id), '<div id="' . $id . '">' . $input . '</div>', $id, $option);
	}

	public function compose(): string
	{
		$content = $this->peopleChooser('compose_recipients');

		$content .= $this->v_utils->v_form_textarea('compose_body');

		$content .= $this->v_utils->v_input_wrapper(false, '<a class="button" id="compose_submit" href="#">' . $this->translationHelper->s('send') . '</a>');

		return '<div id="compose">' . $this->v_utils->v_field($content, $this->translationHelper->s('new_message'), ['class' => 'ui-padding']) . '</div>';
	}

	/**
	 * @param Conversation[] $conversations
	 */
	public function conversationList(array $conversations, array $profiles): string
	{
		$list = '';

		if (!empty($conversations)) {
			foreach ($conversations as $c) {
				if (!$c->lastMessage) {
					/* only show conversations with a message */
					continue;
				}
				$pics = '';
				$title = '';

				if (!empty($c->members)) {
					$pictureWidth = 50;
					$size = 'med';

					if (count($c->members) > 2) {
						$pictureWidth = 25;
						$size = 'mini';
//						shuffle($c->members);
					}

					foreach ($c->members as $m) {
						if ($m == $this->session->id()) {
							continue;
						}
						$pics .= '<img src="' . $this->imageService->img($profiles[$m]->avatar, $size) . '" width="' . $pictureWidth . '" />';
						$title .= ', ' . $profiles[$m]->name;
					}

					if ($c->title === null) {
						$title = substr($title, 2);
					} else {
						$title = $c->title;
					}

					$list .= '<li id="convlist-' . $c->id . '" class="unread-' . (int)$c->hasUnreadMessages . '"><a href="#" onclick="msg.loadConversation(' . $c->id . ');return false;"><span class="pics">' . $pics . '</span><span class="names">' . $this->sanitizerService->plainToHtml($title) . '</span><span class="msg">' . $this->sanitizerService->plainToHtml($c->lastMessage->body) . '</span><span class="time">' . $this->timeHelper->niceDate($c->lastMessage->sentAt->getTimestamp()) . '</span><span class="clear"></span></a></li>';
				}
			}
		} else {
			$list = '<li class="noconv">' . $this->v_utils->v_info($this->translationHelper->s('no_conversations')) . '</li>';
		}

		return $list;
	}

	public function conversationListWrapper(string $list): string
	{
		return $this->v_utils->v_field('<div id="conversation-list"><ul class="linklist conversation-list">' . $list . '</ul></div>', $this->translationHelper->s('conversations'), [], 'fas fa-comments');
	}

	public function conversation(): string
	{
		$out = '
			<div id="msg-conversation" class="corner-all"><ul></ul><div class="loader" style="display:none;"><i class="fas fa-sync fa-spin"></i></div></div>
		';

		$out .= '
			<div id="msg-control">
				<form>
					' . $this->v_utils->v_form_textarea('msg_answer', ['style' => 'width: 88%;', 'nolabel' => true, 'placeholder' => $this->translationHelper->s('write_something')]) . '<input id="conv_submit" type="submit" class="button" name="submit" value="&#xf0a9;" />
				</form>
			</div>';

		return '<div id="msg-conversation-wrapper" style="display:none;">' . $this->v_utils->v_field($out, '', ['class' => 'ui-padding'], 'fas fa-comment', 'msg-conversation-title') . '</div>';
	}
}
