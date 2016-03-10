var Thanks;
(function (Thanks) {
    function setUpHighlighting() {
        var countCells = document.querySelectorAll('table.thanks-grid td.thanks-count');
        for (var i = 0; i < countCells.length; ++i) {
            var countCell = countCells.item(i);
            countCell.addEventListener('mouseover', mouseEnter);
            countCell.addEventListener('mouseout', mouseExit);
        }
    }
    Thanks.setUpHighlighting = setUpHighlighting;
    function highlightCriterion(targetCell, currentCell) {
        if (targetCell.hasAttribute('data-donor-index') && currentCell.hasAttribute('data-donor-index')) {
            if (targetCell.getAttribute('data-donor-index') === currentCell.getAttribute('data-donor-index')) {
                return true;
            }
        }
        if (targetCell.hasAttribute('data-recipient-index') && currentCell.hasAttribute('data-recipient-index')) {
            if (targetCell.getAttribute('data-recipient-index') === currentCell.getAttribute('data-recipient-index')) {
                return true;
            }
        }
        return false;
    }
    function getCellsHitByHighlight(targetCell) {
        var ret = [];
        // find the table
        var tableElement = targetCell;
        while (tableElement != null && tableElement.tagName.toLowerCase() != 'table') {
            tableElement = tableElement.parentElement;
        }
        var table = tableElement;
        var cells = table.getElementsByTagName('td');
        for (var i = 0; i < cells.length; ++i) {
            var cell = cells.item(i);
            if (highlightCriterion(targetCell, cell)) {
                ret.push(cell);
            }
        }
        return ret;
    }
    function mouseEnter(ev) {
        var cell = ev.target;
        // get all the hit cells
        var hitCells = getCellsHitByHighlight(cell);
        // highlight them
        hitCells.forEach(function (c) {
            c.classList.add('highlight');
        });
    }
    function mouseExit(ev) {
        var cell = ev.target;
        // get all the hit cells
        var hitCells = getCellsHitByHighlight(cell);
        // unhighlight them
        hitCells.forEach(function (c) {
            c.classList.remove('highlight');
        });
    }
})(Thanks || (Thanks = {}));
//# sourceMappingURL=thanks.js.map