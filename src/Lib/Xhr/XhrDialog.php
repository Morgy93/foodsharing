<?php

namespace Foodsharing\Lib\Xhr;

use Foodsharing\Lib\View\Utils;
use Foodsharing\Utility\Sanitizer;
use Symfony\Contracts\Translation\TranslatorInterface;

class XhrDialog
{
    private $id;
    private $buttons;
    private $content;
    private $options;
    private $script;
    private $scriptBefore;
    private $scriptAfter;
    private $onopen;
    private $classnames;
    private Utils $viewUtils;
    private TranslatorInterface $translator;
    private Sanitizer $sanitizerService;

    public function __construct($title = false)
    {
        global $container;
        $this->viewUtils = $container->get(Utils::class);
        $this->translator = $container->get('translator'); // TODO TranslatorInterface is an alias
        $this->id = 'd-' . uniqid();
        $this->buttons = [];
        $this->options = [];
        $this->script = '';
        $this->content = '';
        $this->scriptBefore = '';
        $this->onopen = [];
        $this->classnames = [];
        $this->sanitizerService = $container->get(Sanitizer::class);

        if ($title !== false) {
            $this->setTitle($title);
        }

        $this->addOpt('modal', 'true', false);
        $this->addJs('
			$("#' . $this->id . '").dialog({
			    position: { "my": "center", "at": "center" }
			});
		');
    }

    public function addClass($name)
    {
        $this->classnames[] = $name;
    }

    public function addOpt($opt, $value, $quotes = true)
    {
        if ($quotes) {
            $quotes = '"';
        } else {
            $quotes = '';
        }
        $this->options[$opt] = $quotes . $value . $quotes;
    }

    public function setTitle($title)
    {
        $this->addOpt('title', $this->sanitizerService->jsSafe($title, '"'));
    }

    public function addJsBefore($js)
    {
        $this->scriptBefore .= $js;
    }

    public function addJsAfter($js)
    {
        $this->scriptAfter .= $js;
    }

    public function addJs($js)
    {
        $this->script .= $js;
    }

    public function addContent($html)
    {
        $this->content .= $html;
    }

    public function getId()
    {
        return $this->id;
    }

    public function addAbortButton()
    {
        $this->buttons[] = [
            'text' => $this->translator->trans('button.cancel'),
            'click' => '$("#' . $this->id . '").dialog("close");'
        ];
    }

    public function onOpen($js)
    {
        $this->onopen[] = $js;
    }

    public function addButton($text, $click)
    {
        $this->buttons[] = [
            'text' => $text,
            'click' => $click
        ];
    }

    public function setResizeable($val = true)
    {
        $val = $val ? 'true' : 'false';
        $this->addOpt('resizable', $val, false);
    }

    public function addPictureField($id, $label)
    {
        $in_id = $this->id . '-' . $id;

        $this->addContent($this->viewUtils->v_input_wrapper($label, '
			<span id="' . $in_id . '"><i class="far fa-image"></i> ' . $this->translator->trans('upload.image') . '</span>
			<input class="input" type="hidden" name="filename" id="' . $in_id . '-filename" value="" />
			<div class="attach-preview" style="float: right;"></div>
			<div style="width: 10px; height: 10px; overflow: hidden;">
				<form action="/xhrapp?app=main&m=picupload" target="' . $in_id . '-iframe" id="' . $in_id . '-form" method="post" enctype="multipart/form-data">
					<input style="float: right;" type="file" name="' . $id . '" id="' . $in_id . '-file" />
					<input type="hidden" name="id" value="' . $this->id . '" />
					<input type="hidden" name="oid" value="' . $id . '" />
					<input type="hidden" name="inid" value="' . $in_id . '" />
				</form>
				<iframe frameborder="0" style="width: 1px; height: 1px; opacity: 0;" name="' . $in_id . '-iframe"></iframe>
				<div class="clear"></div>
			</div>'
        ));
        $this->addJs('
			$("#' . $in_id . '-file").on("change", function () {
				$("#' . $in_id . '-form").trigger("submit");
				$(".ui-dialog-buttonpane .ui-button").button("option", "disabled", true);
				$(".attach-preview").show();
				$(".attach-preview").html(\'<a href="#" class="preview-thumb attach-load" rel="wallpost-gallery">&nbsp;</a><div class="clear"></div>\');
			});

			$("#' . $in_id . '").button().on("click", function () {
				$("#' . $in_id . '-file").trigger("click");
			});');
    }

    public function noOverflow()
    {
        $this->addOpt('maxHeight', '$(window).height()-30', false);
        $this->addJsAfter('
			if ($("#' . $this->getId() . '").width() > $(window).width()) {
				$("#' . $this->getId() . '").dialog("option", "width", $(window).width() - 30);
			}

			$(window).on("resize", function () {
				$("#' . $this->getId() . '").dialog("option", "maxHeight", $(window).height() - 30);
			});');
    }

    public function xhrout(): array
    {
        $buttons = [];
        foreach ($this->buttons as $b) {
            $buttons[] = '{"text":\'' . $b['text'] . '\', click: function () {' . $b['click'] . '}}';
        }

        $this->addOpt('buttons', '[' . implode(',', $buttons) . ']', false);

        $this->addJs('$("#' . $this->id . '").dialog("option", "position", "center");');

        if (!empty($this->onopen)) {
            $this->addOpt('open', 'function(event, ui) {' . implode(' ', $this->onopen) . '}', false);
        }

        $options = [];
        foreach ($this->options as $opt => $value) {
            $options[] = $opt . ':' . $value;
        }

        $classjs = '';
        if (!empty($this->classnames)) {
            $classjs = '$("#' . $this->id . '").parent().addClass("' . implode(' ', $this->classnames) . '")';
        }

        return [
            'status' => 1,
            'script' => $this->scriptBefore . '
				if ($(".xhrDialog").length > 0) {
					$(".xhrDialog").dialog("close");
					$(".xhrDialog").dialog("destroy");
					$(".xhrDialog").remove();
				}
				$("body").append(\'<div class="xhrDialog" style="display: none;" id="' . $this->id . '"></div>\');
				$("#' . $this->id . '").html(\'' . $this->sanitizerService->jsSafe($this->content) . '\');
				$(".xhrDialog .input.textarea").css("height", "50px");
				$(".xhrDialog .input.textarea").autosize();
				$("#' . $this->id . '").dialog({
					' . implode(',', $options) . '
				});'
                . $this->script . $this->scriptAfter . $classjs
        ];
    }
}
