window.onload = function () {
    function contentOf(node) {
        return node.innerText.trim()
    }

    const attributes = {}
    /**
     *
     * @type {HTMLTableElement}
     */
    const table = document.querySelector('table')
    /**
     *
     * @type {HTMLTableSectionElement}
     */
    const tbody = table.tBodies[0]
    for (let i = 0; i < tbody.rows.length; i++) {
        /**
         *
         * @type {HTMLTableRowElement}
         */
        const tr = tbody.rows[i]
        const name = contentOf(tr.cells[0])
        const fieldTds = tr.cells[1]
            .querySelector('table')
            .querySelectorAll('td:first-child')

        const description = contentOf(tr.cells[1].querySelector('p'))
        const field = attributes[name] = {
            SpecialValue: description.includes('or the special value'),
            Percentage: description.includes('.xx%')
        }

        for (let j = 0; j < fieldTds.length; j++) {
            /**
             * @type {HTMLTableCellElement}
             */
            const cell = fieldTds[j]
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
    }

    console.log(JSON.stringify(attributes, null, 2))
}