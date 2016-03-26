var Thanks;
(function (Thanks) {
    var pinnedHighlight = false;
    function setUpHighlighting() {
        var countCells = document.querySelectorAll('table.thanks-grid td.thanks-count');
        for (var i = 0; i < countCells.length; ++i) {
            var countCell = countCells.item(i);
            countCell.addEventListener('mouseenter', cellMouseEnter);
            countCell.addEventListener('mouseleave', cellMouseExit);
            countCell.addEventListener('click', cellClicked);
        }
        document.body.addEventListener('click', bodyClicked);
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
    function cellMouseEnter(ev) {
        var cell = ev.target;
        addHighlight(false, cell);
    }
    function cellMouseExit(ev) {
        var cell = ev.target;
        removeHighlight(false, cell);
    }
    function cellClicked(ev) {
        var cell = ev.target;
        addHighlight(true, cell);
    }
    function bodyClicked(ev) {
        removeAllHighlights(true);
    }
    function addHighlight(pin, relativeToCell) {
        if (pinnedHighlight && !pin) {
            // not changing this
            return;
        }
        // get all the hit cells
        var hitCells = getCellsHitByHighlight(relativeToCell);
        // highlight them
        hitCells.forEach(function (c) {
            c.classList.add('highlight');
        });
    }
    function removeHighlight(pin, relativeToCell) {
        if (pinnedHighlight && !pin) {
            // not changing this
            return;
        }
        // get all the hit cells
        var hitCells = getCellsHitByHighlight(relativeToCell);
        // highlight them
        hitCells.forEach(function (c) {
            c.classList.remove('highlight');
        });
    }
    function removeAllHighlights(pin) {
        if (pinnedHighlight && !pin) {
            // not changing this
            return;
        }
        var cells = document.getElementsByTagName('td');
        for (var i = 0; i < cells.length; ++i) {
            cells.item(i).classList.remove('highlight');
        }
    }
})(Thanks || (Thanks = {}));
//# sourceMappingURL=thanks.js.map