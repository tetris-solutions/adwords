window.onload = function () {
    function contentOf(node) {
        return node.innerText.trim()
    }

    function list(ls) {
        return Array.prototype.slice.call(ls)
    }

    const attributes = {}

    /**
     *
     * @param {HTMLTableRowElement} tr
     */
    function parseTr(tr) {
        const name = contentOf(tr.cells[0])
        const fieldProperties = tr.cells[1]
            .querySelector('table')
            .querySelectorAll('td:first-child')

        const description = contentOf(tr.cells[1].querySelector('p'))
        const field = attributes[name] = {
            SpecialValue: description.includes('or the special value'),
            Percentage: description.includes('.xx%')
        }

        const showAlways = list(tr.cells[1].querySelectorAll('.showalways'))

        /**
         *
         * @param {HTMLTableRowElement} predicateTr
         */
        function parsePredicateValue(predicateTr) {
            field.PredicateValues[contentOf(predicateTr.cells[0])] = contentOf(predicateTr.cells[1])
        }

        /**
         * @param {HTMLParagraphElement} title
         * @return {Array} denied fields
         */
        function parseIncompatibleFields(title) {
            const deniedFields = []
            /**
             *
             * @type {Element}
             */
            let el = title.nextElementSibling
            while (el && el.querySelector('code')) {
                deniedFields.push(contentOf(el.querySelector('code')))

                el = el.nextElementSibling
            }
            while (el && (
                el.className.includes('exw-collapsed-content') ||
                el.querySelector('code')
            )) {
                deniedFields.push(contentOf(
                    el.className.includes('exw-collapsed-content')
                        ? el
                        : el.querySelector('code')))

                el = el.nextElementSibling
            }

            return deniedFields
        }

        const isListOf = (titleEl, str) => contentOf(titleEl).includes(str)

        showAlways.forEach(listTitle => {
            if (
                isListOf(listTitle, 'Predicate values') &&
                listTitle.nextElementSibling.classList.contains('devsite-table-wrapper')
            ) {
                field.PredicateValues = {}

                list(listTitle.nextElementSibling.querySelectorAll('tr'))
                    .forEach(parsePredicateValue)
            } else if (isListOf(listTitle, 'Not compatible with the following fields')) {
                const d = parseIncompatibleFields(listTitle)

                if (d.length) field.IncompatibleFields = d
            }
        })

        /**
         *
         * @param {HTMLTableCellElement} cell
         */
        function parseProperty(cell) {
            const property = contentOf(cell).replace(/\W/g, '')
            const value = contentOf(cell.nextElementSibling)
            const lowerCasevalue = value.toLowerCase()

            field[property] = value

            if (lowerCasevalue === 'true') {
                field[property] = true
            }

            if (lowerCasevalue === 'false') {
                field[property] = false
            }
        }

        list(fieldProperties).forEach(parseProperty)
    }


    /**
     *
     * @type {HTMLTableElement}
     */
    const table = document.querySelector('table')

    list(table.tBodies[0].rows).forEach(parseTr)

    const textArea = document.createElement('textarea')

    textArea.value = JSON.stringify(attributes, null, 2)
    textArea.style.width = '100vw'
    textArea.style.height = '70vh'

    document.body.insertBefore(textArea, document.body.firstChild)
}