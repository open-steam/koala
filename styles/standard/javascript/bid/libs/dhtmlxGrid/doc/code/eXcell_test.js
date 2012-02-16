//TEST
function CED_test(cell){
		try{
			this.cell = cell;
			this.grid = this.cell.parentNode.grid;
		}catch(er){}
		/**
		*	@desc: method called by grid to start editing
		*/
		this.edit = function(){
		this.val = this.getValue();
		this.obj = document.createElement("TEXTAREA");
		this.obj.style.width = "100%";
		this.obj.style.height = (this.cell.offsetHeight-4)+"px";
		this.obj.style.border = "0px";
		this.obj.style.margin = "0px";
		this.obj.style.padding = "0px";
		this.obj.style.overflow = "hidden";
		this.obj.style.fontSize = "12px";
		this.obj.style.fontFamily = "Arial";
		this.obj.wrap = "soft";
		this.obj.style.textAlign = this.cell.align;
		this.obj.onclick = function(e){(e||event).cancelBubble = true}
		this.obj.value = this.val
		this.cell.innerHTML = "";
		this.cell.appendChild(this.obj);
		this.obj.focus()
		this.obj.focus()
	}
		/**
		*	@desc: get real value of the cell
		*/
		this.getValue = function(val){
		return this.cell.innerHTML.toString();
	}
		/**
		*	@desc: set formated value to the cell
		*/
		this.setValue = function(val){
		if(val.toString().trim()=="")
			val = "&nbsp;";
		if(isNaN(Number(val))){
			this.cell.align = "left";
		}else{
			this.cell.align = "right";
		}
		this.cell.innerHTML = val;
	}
		/**
		*	@desc: this method called by grid to close editor
		*/
		this.detach = function(){
		this.setValue(this.obj.value);
		return this.val!=this.getValue();
	}
}
CED_test.prototype = new CED;