module Thanks
{
    var pinnedHighlight: boolean = false;

    export function setUpHighlighting(): void
    {
        var countCells: NodeListOf<Element> = document.querySelectorAll('table.thanks-grid td.thanks-count');
        for (var i: number = 0; i < countCells.length; ++i)
        {
            var countCell = <HTMLTableDataCellElement>countCells.item(i);
            countCell.addEventListener('mouseenter', cellMouseEnter);
            countCell.addEventListener('mouseleave', cellMouseExit);
            countCell.addEventListener('click', cellClicked);
        }
        
        var spacer = document.querySelector('table.thanks-grid td.top-left-spacer');
        if (spacer !== null)
        {
            spacer.addEventListener('click', spacerClicked);
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

    function cellMouseEnter(ev: MouseEvent): void
    {
        var cell = <HTMLTableDataCellElement>ev.target;
        addHighlight(false, cell);
    }

    function cellMouseExit(ev: MouseEvent): void
    {
        var cell = <HTMLTableDataCellElement>ev.target;
        removeHighlight(false, cell);
    }
    
    function cellClicked(ev: MouseEvent): void
    {
        var cell = <HTMLTableDataCellElement>ev.target;
        addHighlight(true, cell);
    }
    
    function spacerClicked(ev: MouseEvent): void
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
    }
}
