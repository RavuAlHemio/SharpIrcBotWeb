"use strict";
var Counters;
(function (Counters) {
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
            headerCell.addEventListener('click', (function (tbl, index) {
                return function () {
                    sort(tbl, index);
                };
            })(table, i));
        }
    }
    function sort(table, columnIndex) {
        var curSortColumnIndex = 0;
        var curSortColumnIndexString = table.dataset.sortColumnIndex;
        if (typeof curSortColumnIndexString !== 'undefined') {
            curSortColumnIndex = +curSortColumnIndexString;
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
        var cmpFunc = getTableRowCompareFunc(columnIndex, reverseSort);
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
    }
    function getTableRowCompareFunc(colIndex, reverse) {
        if (reverse) {
            return function (a, b) {
                return tableRowCompareFunc(colIndex, a, b);
            };
        }
        else {
            return function (a, b) {
                return -tableRowCompareFunc(colIndex, a, b);
            };
        }
    }
    function tableRowCompareFunc(colIndex, a, b) {
        var aCell = a.querySelectorAll('td').item(colIndex);
        var bCell = b.querySelectorAll('td').item(colIndex);
        var aValue = aCell.textContent;
        var bValue = bCell.textContent;
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
        var integral = /^[0-9]+$/;
        if (integral.test(aValue) && integral.test(bValue)) {
            // numeric compare
            return ((+bValue) - (+aValue));
        }
        return aValue.localeCompare(bValue);
    }
})(Counters || (Counters = {}));
//# sourceMappingURL=counters.js.map