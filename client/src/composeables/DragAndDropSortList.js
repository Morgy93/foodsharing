import Vue from 'vue'

/**
 *
 * @param {HTMLElement} listElementRef
 * @param {string[]} itemsRef
 * @param {HTMLElement[]} [dragElementsRef]
 */
export function useDragAndDropSortableList (listElementRef, itemsRef, dragElementsRef) {
  function setupDragAndDropList () {
    const listElement = listElementRef.value
    normalizeList(listElement)
    const items = itemsRef.value
    console.log('setup drag and drop', [listElement, items])
    if (!listElement) throw Error('listElement is not set')
    if (!items) throw Error('items are not set')
    if (listElement.$children.length !== items.length) throw Error('ListElement child count is not matching items array length')

    const elements = Array.from(listElement.$children)

    function prepareListItem (item) {
      normalizeListItem(item)
      item.setAttribute('draggable', true)
      item.addEventListener('dragstart', onDragStart)
      item.addEventListener('drop', onDrop)
      item.addEventListener('dragover', allowDrop)
    }

    /**
         *
         * @param event
         */
    function onDragStart (event) {
      event.dataTransfer.setData('position', elements.indexOf(event.currentTarget))
    }

    /**
         *
         * @param {DragEvent} event
         */
    function onDrop (event) {
      const sourcePosition = parseInt(event.dataTransfer.getData('position'))
      const dropPosition = elements.indexOf(event.currentTarget)
      console.log(`drop ${sourcePosition} onto ${dropPosition}`)
      console.log('before:', items.map(item => item))
      // reposition
      const item = items.splice(sourcePosition, 1)[0]
      items.splice(dropPosition, 0, item)
      console.log('after:', items.map(item => item))
    }

    /**
         *
         * @param event
         */
    function allowDrop (event) {
      event.preventDefault()
    }

    elements.forEach(prepareListItem)
  }

  return { setupDragAndDropList }
}

function normalizeList (HTMLElementOrVueComponent) {
  if (HTMLElementOrVueComponent instanceof HTMLElement) {
    Object.defineProperty(HTMLElementOrVueComponent, '$children', {
      get () {
        return this.children
      },
      set (value) {
        this.children = value
      },
    })
  }
}

function normalizeListItem (HTMLElementOrVueComponent) {
  if (HTMLElementOrVueComponent instanceof Vue) {
    Object.defineProperty(HTMLElementOrVueComponent, 'setAttribute', {
      value: function (qualifiedName, value) {
        console.log(HTMLElementOrVueComponent)
      },
    })
  }
}
