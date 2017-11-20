"use strict";
module Counters
{
    export function setUpSortingOnLoad(): void
    {
        document.addEventListener('DOMContentLoaded', setUpSorting);
    }

    export function setUpSorting(): void
    {
        let sortableTables: NodeListOf<HTMLTableElement>
            = <NodeListOf<HTMLTableElement>>document.querySelectorAll('table.sortable');
        for (let i: number = 0; i < sortableTables.length; ++i)
        {
            setUpSortingForTable(sortableTables.item(i));
        }
    }

    function setUpSortingForTable(table: HTMLTableElement): void
    {
        let headerCells: NodeListOf<HTMLTableHeaderCellElement>
            = <NodeListOf<HTMLTableHeaderCellElement>>table.querySelectorAll('th');
        for (let i: number = 0; i < headerCells.length; ++i)
        {
            let headerCell: HTMLTableHeaderCellElement = <HTMLTableHeaderCellElement>headerCells.item(i);
            headerCell.addEventListener('click', (function (tbl: HTMLTableElement, index: number) {
                return function () {
                    sort(tbl, index);
                };
            })(table, i));
        }
    }

    function sort(table: HTMLTableElement, columnIndex: number): void
    {
        let curSortColumnIndex: number = 0;

        let curSortColumnIndexString: string|undefined = table.dataset.sortColumnIndex;
        if (typeof curSortColumnIndexString !== 'undefined')
        {
            curSortColumnIndex = +curSortColumnIndexString;
        }

        let reverseSort: boolean = false;
        if (curSortColumnIndex == columnIndex)
        {
            // same header again; reverse the current sort
            let isReversed: boolean = (table.dataset.sortReversed === 'true');
            reverseSort = !isReversed;
        }

        // categorize the rows into arrays
        let rowList: NodeListOf<HTMLTableRowElement> = table.querySelectorAll('tr');
        let preRows: HTMLTableRowElement[] = [];
        let entryRows: HTMLTableRowElement[] = [];
        let postRows: HTMLTableRowElement[] = [];
        let seenEntries: boolean = false;
        for (let i = 0; i < rowList.length; ++i)
        {
            let row: HTMLTableRowElement = rowList.item(i);

            if (row.classList.contains("entry"))
            {
                entryRows.push(row);
                seenEntries = true;
            }
            else if (seenEntries)
            {
                postRows.push(row);
            }
            else
            {
                preRows.push(row);
            }
        }

        // sort the entry rows
        let cmpFunc: (a: HTMLTableRowElement, b: HTMLTableRowElement) => number
            = getTableRowCompareFunc(columnIndex, reverseSort);
        entryRows.sort(cmpFunc);

        // add them to the table in order
        for (let i = 0; i < preRows.length; ++i)
        {
            table.appendChild(preRows[i]);
        }
        for (let i = 0; i < entryRows.length; ++i)
        {
            table.appendChild(entryRows[i]);
        }
        for (let i = 0; i < postRows.length; ++i)
        {
            table.appendChild(postRows[i]);
        }

        // store
        table.dataset.sortColumnIndex = "" + columnIndex;
        table.dataset.sortReversed = "" + reverseSort;
    }

    function getTableRowCompareFunc(colIndex: number, reverse: boolean): (a: HTMLTableRowElement, b: HTMLTableRowElement) => number
    {
        if (reverse)
        {
            return function (a: HTMLTableRowElement, b: HTMLTableRowElement) {
                return tableRowCompareFunc(colIndex, a, b);
            };
        }
        else
        {
            return function (a: HTMLTableRowElement, b: HTMLTableRowElement) {
                return -tableRowCompareFunc(colIndex, a, b);
            };
        }
    }

    function tableRowCompareFunc(colIndex: number, a: HTMLTableRowElement, b: HTMLTableRowElement): number
    {
        let aCell: HTMLTableDataCellElement =
            a.querySelectorAll('td').item(colIndex);
        let bCell: HTMLTableDataCellElement =
            b.querySelectorAll('td').item(colIndex);

        let aValue: string|null = aCell.textContent;
        let bValue: string|null = bCell.textContent;

        // nulls first
        if (aValue === null)
        {
            if (bValue === null)
            {
                // a(null) == b(null)
                return 0;
            }

            // a(null) < b(notnull)
            return -1;
        }
        else if (bValue === null)
        {
            // a(notnull) > b(null)
            return 1;
        }

        let integral: RegExp = /^[0-9]+$/;
        if (integral.test(aValue) && integral.test(bValue))
        {
            // numeric compare
            return ((+bValue) - (+aValue));
        }

        return aValue.localeCompare(bValue);
    }
}
