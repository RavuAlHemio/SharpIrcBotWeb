"use strict";
module Thanks
{
    var pinnedHighlight: boolean = false;

    export function setUpHighlighting(): void
    {
        var countCells: NodeListOf<Element> = document.querySelectorAll('table.thanks-grid td.thanks-count');
        for (var i: number = 0; i < countCells.length; ++i)
        {
            var countCell = <HTMLTableDataCellElement>countCells.item(i);
            countCell.addEventListener('mouseenter', bind(countCell, cellMouseEnter));
            countCell.addEventListener('mouseleave', bind(countCell, cellMouseExit));
            countCell.addEventListener('click', bind(countCell, cellClicked));
        }

        var spacer = document.querySelector('table.thanks-grid td.top-left-spacer');
        if (spacer !== null)
        {
            spacer.addEventListener('click', spacerClicked);
        }
    }

    function bind<T, U>(val: T, func: (v: T) => U): () => U
    {
        return ((arg) => (() => func(arg)))(val);
    }

    function highlightCriterion(targetCell: HTMLTableCellElement, currentCell: HTMLTableCellElement): boolean
    {
        if (targetCell.hasAttribute('data-donor-index') && currentCell.hasAttribute('data-donor-index'))
        {
            if (targetCell.getAttribute('data-donor-index') === currentCell.getAttribute('data-donor-index'))
            {
                return true;
            }
        }
        if (targetCell.hasAttribute('data-recipient-index') && currentCell.hasAttribute('data-recipient-index'))
        {
            if (targetCell.getAttribute('data-recipient-index') === currentCell.getAttribute('data-recipient-index'))
            {
                return true;
            }
        }
        return false;
    }

    function getCellsHitByHighlight(targetCell: HTMLTableCellElement): HTMLTableCellElement[]
    {
        var ret: HTMLTableCellElement[] = [];

        // find the table
        var tableElement: Element = targetCell;
        while (tableElement != null && tableElement.tagName.toLowerCase() != 'table')
        {
            tableElement = tableElement.parentElement;
        }
        var table: HTMLTableElement = <HTMLTableElement>tableElement;
        var cells: NodeListOf<HTMLTableDataCellElement> = table.getElementsByTagName('td');

        for (var i: number = 0; i < cells.length; ++i)
        {
            var cell: HTMLTableDataCellElement = cells.item(i);
            if (highlightCriterion(targetCell, cell))
            {
                ret.push(cell);
            }
        }

        return ret;
    }

    function cellMouseEnter(cell: HTMLTableDataCellElement): void
    {
        addHighlight(false, cell);
    }

    function cellMouseExit(cell: HTMLTableDataCellElement): void
    {
        removeHighlight(false, cell);
    }

    function cellClicked(cell: HTMLTableDataCellElement): void
    {
        addHighlight(true, cell);
    }

    function spacerClicked(): void
    {
        removeAllHighlights(true);
    }

    function addHighlight(pin: boolean, relativeToCell: HTMLTableDataCellElement): void
    {
        if (pinnedHighlight && !pin)
        {
            // not changing this
            return;
        }
        else if (pin)
        {
            pinnedHighlight = true;
        }

        // get all the hit cells
        var hitCells = getCellsHitByHighlight(relativeToCell);

        // highlight them
        hitCells.forEach(function (c: HTMLTableCellElement) {
            c.classList.add('highlight');
        });
    }

    function removeHighlight(pin: boolean, relativeToCell: HTMLTableDataCellElement): void
    {
        if (pinnedHighlight && !pin)
        {
            // not changing this
            return;
        }
        else if (pin)
        {
            pinnedHighlight = true;
        }

        // get all the hit cells
        var hitCells = getCellsHitByHighlight(relativeToCell);

        // highlight them
        hitCells.forEach(function (c: HTMLTableCellElement) {
            c.classList.remove('highlight');
        });
    }

    function removeAllHighlights(pin: boolean): void
    {
        if (pinnedHighlight && !pin)
        {
            // not changing this
            return;
        }

        var cells = document.getElementsByTagName('td');
        for (var i: number = 0; i < cells.length; ++i)
        {
            cells.item(i).classList.remove('highlight');
        }

        pinnedHighlight = false;
    }
}
