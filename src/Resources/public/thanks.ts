module Thanks
{
    export function setUpHighlighting(): void
    {
        var countCells: NodeListOf<Element> = document.querySelectorAll('table.thanks-grid td.thanks-count');
        for (var i: number = 0; i < countCells.length; ++i)
        {
            var countCell = <HTMLTableDataCellElement>countCells.item(i);
            countCell.addEventListener('mouseover', mouseEnter);
            countCell.addEventListener('mouseout', mouseExit);
        }
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

    function mouseEnter(ev: MouseEvent): void
    {
        var cell = <HTMLTableDataCellElement>ev.target;

        // get all the hit cells
        var hitCells = getCellsHitByHighlight(cell);

        // highlight them
        hitCells.forEach(function (c: HTMLTableCellElement) {
            c.classList.add('highlight');
        });
    }

    function mouseExit(ev: MouseEvent): void
    {
        var cell = <HTMLTableDataCellElement>ev.target;

        // get all the hit cells
        var hitCells = getCellsHitByHighlight(cell);

        // unhighlight them
        hitCells.forEach(function (c: HTMLTableCellElement) {
            c.classList.remove('highlight');
        });
    }
}
