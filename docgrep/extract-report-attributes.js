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
        const tds = tr.cells[1].querySelectorAll('td')
        for (let j = 0; j < tds.length; j++) {
            /**
             * @type {HTMLTableCellElement}
             */
            const cell = tds[j]
            if (contentOf(cell) === 'XML Attribute') {
                attributes[name] = contentOf(cell.nextElementSibling)
            }
        }
    }

    console.log(JSON.stringify(attributes, null, 2))
}