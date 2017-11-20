"use strict";
var Counters;
(function (Counters) {
    function setUpSortingOnLoad() {
        document.addEventListener('DOMContentLoaded', setUpSorting);
    }
    Counters.setUpSortingOnLoad = setUpSortingOnLoad;
    function setUpSorting() {
        var sortableTables = document.querySelectorAll('table.sortable');
        for (var i = 0; i < sortableTables.length; ++i) {
            setUpSortingForTable(sortableTables.item(i));
        }
    }
    Counters.setUpSorting = setUpSorting;
    function setUpSortingForTable(table) {
        var headerCells = table.querySelectorAll('th');
        for (var i = 0; i < headerCells.length; ++i) {
            var headerCell = headerCells.item(i);
            headerCell.addEventListener('click', getSortFunction(headerCell, table, i));
            // UX: now that sorting is possible, change cursor in table header to hand
            headerCell.style.cursor = 'pointer';
        }
    }
    function getSortFunction(headerCell, table, index) {
        return function () {
            sort(headerCell, table, index, 0);
        };
    }
    function sort(headerCell, table, columnIndex, tieBreakerIndex) {
        if (tieBreakerIndex === void 0) { tieBreakerIndex = -1; }
        var curSortColumnIndex = 0;
        var curSortColumnIndexString = table.dataset.sortColumnIndex;
        if (typeof curSortColumnIndexString !== 'undefined') {
            curSortColumnIndex = +curSortColumnIndexString;
        }
        if (!isFinite(curSortColumnIndex)) {
            curSortColumnIndex = 0;
        }
        var reverseSort = false;
        if (curSortColumnIndex == columnIndex) {
            // same header again; reverse the current sort
            var isReversed = (table.dataset.sortReversed === 'true');
            reverseSort = !isReversed;
        }
        // categorize the rows into arrays
        var rowList = table.querySelectorAll('tr');
        var preRows = [];
        var entryRows = [];
        var postRows = [];
        var seenEntries = false;
        for (var i = 0; i < rowList.length; ++i) {
            var row = rowList.item(i);
            if (row.classList.contains("entry")) {
                entryRows.push(row);
                seenEntries = true;
            }
            else if (seenEntries) {
                postRows.push(row);
            }
            else {
                preRows.push(row);
            }
        }
        // sort the entry rows
        var cmpFunc = getTableRowCompareFunc(columnIndex, tieBreakerIndex, reverseSort);
        entryRows.sort(cmpFunc);
        // add them to the table in order
        for (var i = 0; i < preRows.length; ++i) {
            table.appendChild(preRows[i]);
        }
        for (var i = 0; i < entryRows.length; ++i) {
            table.appendChild(entryRows[i]);
        }
        for (var i = 0; i < postRows.length; ++i) {
            table.appendChild(postRows[i]);
        }
        // store
        table.dataset.sortColumnIndex = "" + columnIndex;
        table.dataset.sortReversed = "" + reverseSort;
        // UX: make current sort information visible
        // => remove sort-column markers from elsewhere
        var headers = table.querySelectorAll('th');
        for (var i = 0; i < headers.length; ++i) {
            var header = headers.item(i);
            header.classList.remove('sort-key');
            header.classList.remove('sort-asc');
            header.classList.remove('sort-desc');
        }
        // => add sort-column marker to sorted column
        headerCell.classList.add('sort-key');
        // => add direction of marker
        headerCell.classList.add(reverseSort ? 'sort-desc' : 'sort-asc');
    }
    function getTableRowCompareFunc(colIndex, tieBreakerIndex, reverse) {
        return function (a, b) {
            return tableRowCompareFunc(colIndex, tieBreakerIndex, reverse, a, b);
        };
    }
    function tableRowCompareFunc(colIndex, tieBreakerIndex, reverse, a, b) {
        var aCells = a.querySelectorAll('td');
        var bCells = b.querySelectorAll('td');
        // compare by regular index
        var aValue = aCells.item(colIndex).textContent;
        var bValue = bCells.item(colIndex).textContent;
        var ret = valueCompareFunc(aValue, bValue);
        if (reverse) {
            ret = -ret;
        }
        if (ret == 0 && tieBreakerIndex >= 0 && colIndex != tieBreakerIndex) {
            // try breaking the tie
            // (tiebreaks are always ascending!)
            var aTieValue = aCells.item(tieBreakerIndex).textContent;
            var bTieValue = bCells.item(tieBreakerIndex).textContent;
            ret = valueCompareFunc(aTieValue, bTieValue);
        }
        return ret;
    }
    function valueCompareFunc(aValue, bValue) {
        // nulls first
        if (aValue === null) {
            if (bValue === null) {
                // a(null) == b(null)
                return 0;
            }
            // a(null) < b(notnull)
            return -1;
        }
        else if (bValue === null) {
            // a(notnull) > b(null)
            return 1;
        }
        var numericRE = /^([0-9]+(?:\.[0-9]*)?)[%]?$/;
        var numericA = numericRE.exec(aValue);
        var numericB = numericRE.exec(bValue);
        if (numericA != null && numericB != null) {
            // numeric compare
            var aNumber = +(numericA[1]);
            var bNumber = +(numericB[1]);
            if (aNumber < bNumber) {
                return -1;
            }
            else if (aNumber == bNumber) {
                return 0;
            }
            return 1;
        }
        return aValue.localeCompare(bValue);
    }
})(Counters || (Counters = {}));
//# sourceMappingURL=counters.js.map