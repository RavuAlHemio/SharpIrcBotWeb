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
            = getTableRowCompareFunc(columnIndex, 0, reverseSort);
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

    function getTableRowCompareFunc(colIndex: number, tieBreakerIndex: number, reverse: boolean): (a: HTMLTableRowElement, b: HTMLTableRowElement) => number
    {
        if (reverse)
        {
            return function (a: HTMLTableRowElement, b: HTMLTableRowElement) {
                return -tableRowCompareFunc(colIndex, tieBreakerIndex, a, b);
            };
        }
        else
        {
            return function (a: HTMLTableRowElement, b: HTMLTableRowElement) {
                return tableRowCompareFunc(colIndex, tieBreakerIndex, a, b);
            };
        }
    }

    function tableRowCompareFunc(colIndex: number, tieBreakerIndex: number, a: HTMLTableRowElement, b: HTMLTableRowElement): number
    {
        let aCells: NodeListOf<HTMLTableDataCellElement> = a.querySelectorAll('td');
        let bCells: NodeListOf<HTMLTableDataCellElement> = b.querySelectorAll('td');

        // compare by regular index
        let aValue: string|null = aCells.item(colIndex).textContent;
        let bValue: string|null = bCells.item(colIndex).textContent;

        let ret: number = valueCompareFunc(aValue, bValue);

        if (ret == 0 && colIndex != tieBreakerIndex)
        {
            // try breaking the tie
            let aTieValue: string|null = aCells.item(tieBreakerIndex).textContent;
            let bTieValue: string|null = bCells.item(tieBreakerIndex).textContent;
            ret = valueCompareFunc(aTieValue, bTieValue);
        }

        return ret;
    }
    
    function valueCompareFunc(aValue: string|null, bValue: string|null): number
    {
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

        let numeric: RegExp = /^[0-9]+(?:\.[0-9]*)?$/;
        if (numeric.test(aValue) && numeric.test(bValue))
        {
            // numeric compare
            let aNumber: number = +aValue;
            let bNumber: number = +bValue;

            if (aNumber < bNumber)
            {
                return -1;
            }
            else if (aNumber == bNumber)
            {
                return 0;
            }
            return 1;
        }

        return aValue.localeCompare(bValue);
    }
}
