/**
*     @desc: enables block selection mode in grid
*     @type: public
*     @topic: 0
*/
dhtmlXGridObject.prototype.enableBlockSelection = function()
{
	var self = this;
	this.obj.onmousedown = function(e) {e = e||event; self._OnSelectionStart(e, this);}
	this._CSVRowDelimiter = '\n';
	this.setOnResize( function() {self._HideSelection(); return true;});
}

dhtmlXGridObject.prototype._OnSelectionStart = function(event, obj)
{
	var self = this;
	var pos = this.getPosition(this.obj);
	var x = event.clientX - pos[0] +document.body.scrollLeft;
	var y = event.clientY - pos[1] +document.body.scrollTop;
	this._CreateSelection(x, y);
	var src = event.srcElement || event.target;
	if (src == this._selectionObj) {
		this._HideSelection();
		this._startSelectionCell = null;
	} else {
	    while (src.tagName.toLowerCase() != 'td')
	        src = src.parentNode;
	    this._startSelectionCell = src;
	}
	    //this._ShowSelection();
	    this.obj.onmousedown = null;
	    this.obj.onmousemove = function(e) {e = e||event; e.returnValue = false;  self._OnSelectionMove(e, this);}
	    this.obj.onmouseup = function(e) {e = e||event; self._OnSelectionStop(e, this);}
	
}

dhtmlXGridObject.prototype._OnSelectionMove = function(event, obj)
{
	if(this._startSelectionCell==null){
		var src = event.srcElement || event.target;
		while (src.tagName.toLowerCase() != 'td')
	        src = src.parentNode;
	    this._startSelectionCell = src;
	}
	
	this._ShowSelection();
	var pos = this.getPosition(this.obj);
	var X = event.clientX - pos[0]+document.body.scrollLeft;
	var Y = event.clientY - pos[1]+document.body.scrollTop;
	//window.status = pos[0]+'+'+pos[1];
	var prevX = this._selectionObj.startX;
	var prevY = this._selectionObj.startY;
	var diffX = X - prevX;
	var diffY = Y - prevY;
	if (diffX < 0) {
        this._selectionObj.style.left = this._selectionObj.startX + diffX + 1;
        diffX = 0 - diffX;
	} else {
		this._selectionObj.style.left = this._selectionObj.startX - 3;
	}
	if (diffY < 0) {
		this._selectionObj.style.top = this._selectionObj.startY + diffY + 1;
        diffY = 0 - diffY;
	} else {
		this._selectionObj.style.top = this._selectionObj.startY - 3;
	}
    this._selectionObj.style.width = diffX + 'px';
    this._selectionObj.style.height = diffY + 'px';
}

dhtmlXGridObject.prototype._OnSelectionStop = function(event, obj)
{
	var self = this;
	this.obj.onmousedown = function(e) {e = e||event; self._OnSelectionStart(e, this);}
	this.obj.onmousemove = null;
	this.obj.onmouseup = null;
	if ( parseInt( this._selectionObj.style.width ) < 2 && parseInt( this._selectionObj.style.height ) < 2) {
		this._HideSelection();
	} else {
	    var src = event.srcElement || event.target;
	    while (src.tagName.toLowerCase() != 'td')
	        src = src.parentNode;
	    this._stopSelectionCell = src;
	    this._selectionArea = this._RedrawSelectionPos(this._startSelectionCell, this._stopSelectionCell);
	}
}

dhtmlXGridObject.prototype._RedrawSelectionPos = function(LeftTop, RightBottom)
{
//	debugger;
//	td._cellIndex
//
//	getRowIndex
	var pos = {};
	pos.LeftTopCol = LeftTop._cellIndex;
	pos.LeftTopRow = this.getRowIndex( LeftTop.parentNode.idd );
	pos.RightBottomCol = RightBottom._cellIndex;
	pos.RightBottomRow = this.getRowIndex( RightBottom.parentNode.idd );

	var LeftTop_width = LeftTop.offsetWidth;
	var LeftTop_height = LeftTop.offsetHeight;
	LeftTop = this.getPosition(LeftTop, this.obj);

	var RightBottom_width = RightBottom.offsetWidth;
	var RightBottom_height = RightBottom.offsetHeight;
	RightBottom = this.getPosition(RightBottom, this.obj);

    if (LeftTop[0] < RightBottom[0]) {
		var Left = LeftTop[0];
		var Right = RightBottom[0] + RightBottom_width;
    } else {
    	var foo = pos.RightBottomCol;
        pos.RightBottomCol = pos.LeftTopCol;
        pos.LeftTopCol = foo;
		var Left = RightBottom[0];
		var Right = LeftTop[0] + LeftTop_width;
    }

    if (LeftTop[1] < RightBottom[1]) {
		var Top = LeftTop[1];
		var Bottom = RightBottom[1] + RightBottom_height;
    } else {
    	var foo = pos.RightBottomRow;
        pos.RightBottomRow = pos.LeftTopRow;
        pos.LeftTopRow = foo;
		var Top = RightBottom[1];
		var Bottom = LeftTop[1] + LeftTop_height;
    }

    var Width = Right - Left;
    var Height = Bottom - Top;

	this._selectionObj.style.left = Left + 'px';
	this._selectionObj.style.top = Top + 'px';
	this._selectionObj.style.width =  Width  + 'px';
	this._selectionObj.style.height = Height + 'px';
	return pos;
}

dhtmlXGridObject.prototype._CreateSelection = function(x, y)
{
	if (this._selectionObj == null) {
		var div = document.createElement('div');
		div.style.position = 'absolute';
        div.style.display = 'none';
        div.className = 'dhtmlxGrid_selection';
		this._selectionObj = div;
		this.obj.appendChild(this._selectionObj);
	}
    //this._selectionObj.style.border = '1px solid #83abeb';
    this._selectionObj.style.width = '0px';
    this._selectionObj.style.height = '0px';
    //this._selectionObj.style.border = '0px';
	this._selectionObj.style.left = x + 'px';
	this._selectionObj.style.top  = y + 'px';
    this._selectionObj.startX = x;
    this._selectionObj.startY = y;
}

dhtmlXGridObject.prototype._ShowSelection = function()
{
	if (this._selectionObj)
	    this._selectionObj.style.display = '';
}

dhtmlXGridObject.prototype._HideSelection = function()
{
	if (this._selectionObj)
	    this._selectionObj.style.display = 'none';
    this._selectionArea = null;
}
/**
*     @desc: copy content of block selection into clipboard
*     @type: public
*     @topic: 0
*/
dhtmlXGridObject.prototype.copyBlockToClipboard = function()
{
	if ( this._selectionArea != null ) {
		var serialized = new Array();
		this._agetm = this._mathSerialization ? "getMathValue" : "getValue";
		for (var i=this._selectionArea.LeftTopRow; i<=this._selectionArea.RightBottomRow; i++) {
			var data = this._serializeRowToCVS(this.rowsCol[i], null,  this._selectionArea.LeftTopCol, this._selectionArea.RightBottomCol+1);
			serialized[serialized.length] = data.substr( data.indexOf( this._csvDelim ) + 1 );	//remove row ID and add to array
		}
		serialized = serialized.join(this._CSVRowDelimiter);
		this.toClipBoard(serialized);
	}
}
/**
*     @desc: paste content of clipboard into block selection of grid
*     @type: public
*     @topic: 0
*/
dhtmlXGridObject.prototype.pasteBlockFromClipboard = function()
{
	var serialized = this.fromClipBoard();
    if (this._selectionArea != null) {
        var startRow = this._selectionArea.LeftTopRow;
        var startCol = this._selectionArea.LeftTopCol;
    } else if (this.cell != null) {
        var startRow = this.getRowIndex( this.cell.parentNode.idd );
        var startCol = this.cell._cellIndex;
    } else {
        return false;
    }

    serialized = serialized.split(this._CSVRowDelimiter);
    for (var i=0; i<serialized.length; i++) {
        serialized[i] = serialized[i].split(this._csvDelim);
    }
    var endRow = startRow+serialized.length;
    var endCol = startCol+serialized[0].length;
    if (endCol > this._cCount)
		endCol = this._cCount;
    var k = 0;
    for (var i=startRow; i<endRow; i++) {
        var row = this.rowsCol[i];
        if (!row)
        	continue;
        var l = 0;
        for (var j=startCol; j<endCol; j++) {
        	var ed = this.cells3(row, j);
        	ed[ ed.setLabel ? "setLabel" : "setValue" ]( serialized[ k ][ l++ ] );
        }
        k++;
    }
}