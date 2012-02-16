
		/**
		*	@desc: enable/disable dynamic loading
		*	@type: public
		*	@before_init: 1
		*	@param: mode - true/false to enable/disable 
		*	@param: totalRows - max count of rows, optional
		*	@param: bufferSize - size of buffer, autodect by default
		*	@param: renderSize - size of rows rendered at one time, optional, by default equal to view size
		*	@topic: 0
		*/
 dhtmlXGridObject.prototype.enableSmartRendering = function(mode,totalRows,bufferSize,renderSize){
            this._dload=convertStringToBoolean(mode)
			if (!this._dload) {
                if (this.deleteRow_WSRD){
	    	        this.deleteRow=this.deleteRow_WSRD;
	            	this._insertRowAt=this._insertRowAt_WSRD;
					this._initDrF=false;
				}
				return;
			}
			if (!this._srdh) this._srdh=20;

			if (!this.deleteRow_WSRD){
	            this.deleteRow_WSRD=this.deleteRow;
    	        this.deleteRow=this.deleteRow_WSRDA;

        	    this._insertRowAt_WSRD=this._insertRowAt;
            	this._insertRowAt=this._insertRowAt_WSRDA;
				}


            this._dInc=12;
            this._dl_start=new Array();
            this._limitC=this.limit=totalRows;

            this.multiLine=false;
            this._dloadSize=Math.floor(parseInt(this.entBox.style.height)/this._srdh)+2; //rough, but will work
			this.renderSize=renderSize;
          	this.obj.className+=" row20px";

            if (this.hdr.childNodes[1])
                this._initD();
            else
                this._initDrF=true;

		}

		/**
		*	@desc: return data about current view
		*	@type: public
		*	@returns: array [ start index, view size, total rows]
		*	@topic: 0
		*/
 dhtmlXGridObject.prototype.getStateOfView = function(){
 		if (!this._srdh) this._srdh=20;
 		return [
            Math.floor(this.objBox.scrollTop/this._srdh),
			Math.ceil(parseInt(this.objBox.offsetHeight)/this._srdh),
			this.limit
			];
		}
/**
*   @desc:  init smart rendering view
*   @type:  private
*/

 dhtmlXGridObject.prototype._initD = function(){
             if ((this.fldSort)&&(this.fldSort.length))
                for (var i=0; i<this.fldSort.length; i++) this.fldSort[i]="na";

            if (this.limit)
                this._fastAddRowSpacer(0,this.limit*this._srdh);


            this._initDrF=false;
            }


		/**
		*	@desc: enable/disable DOM limit
		*	@type: private
		*	@before_init: 1
		*	@param: url - url to xml feed
		*	@param: limit - maximum number of row in table
		*	@topic: 0
		*/
 dhtmlXGridObject.prototype.enableDOMLimit = function(mode,limit){
    if (!convertStringToBoolean(mode)) return;
    this._dom_limit=limit||1000;
 }




/**
*   @desc:  add row from buffer in SRND view
*   @type:  private
*/
 dhtmlXGridObject.prototype._addFromBufferSR=function(j){
                    if ((!this.rowsCol[j])||(this.rowsCol[j]._sRow))
                        this._splitRowAt(j);
                   else
                    if ((this.rowsBuffer[1][j])&&(this.rowsBuffer[1][j].tagName=="TR")){
                        this.rowsCol[j].parentNode.insertBefore(this.rowsBuffer[1][j],this.rowsCol[j]);
                        this.rowsCol[j].parentNode.removeChild(this.rowsCol[j]);
                        this.rowsCol[j].grid=null;
                        this.rowsCol[j]=this.rowsBuffer[1][j];
                    }


                if (this.rowsBuffer[1][j].tagName=="row"){
                    if (this._cssEven){
                      if (j%2==1) this.rowsCol[j].className=this._cssUnEven;
                      else this.rowsCol[j].className=this._cssEven;
                    }
                    this.changeRowId(this.rowsCol[j].idd,this.rowsBuffer[1][j].getAttribute("id"));
                    this._fillRowFromXML(this.rowsCol[j],this.rowsBuffer[1][j],-1);
                }
                else {
                    this.rowsAr[this.rowsBuffer[1][j].idd]=this.rowsBuffer[1][j];
                    this.rowsBuffer[1][j]._sRow=this.rowsBuffer[1][j]._rLoad=false;
                    }



                    this.rowsCol[j]._rLoad=false;
                    this.rowsBuffer[1][j]=null;
}

/**
*   @desc:  check if rows must be added, load XML if necessary
*   @type:  private
*/
 dhtmlXGridObject.prototype._askRealRows=function(pos,afterCall){
 		if ((this.renderSize)&&(this.renderSize>this._dloadSize))
	 		var cdload=this.renderSize;
		else
			var cdload=this._dloadSize;
        if (!this.limit){
                  this._dl_start[0]=[0,cdload];
                  var loader = new dtmlXMLLoaderObject(this._askRealRows2,this);
				  if (this._dloadStr)
				      loader.loadXMLString(this._dloadStr);
				  else
	                  loader.loadXML(this._dload+((this._dload.indexOf("?")!=-1)?"&":"?")+"posStart="+0+"&sn="+(new Date()).valueOf());
                  //if (this.onXLS) this.onXLS(this);
                  return true;
                  }
        var gi=pos||Math.floor(this.objBox.scrollTop/this._srdh);
        if ((this._dom_limit)&&(this.obj._rowslength()>this._dom_limit))
            {

            }
        //check if data required
        if (gi>(this.limit-cdload)) gi=this.limit-cdload;
		if (gi<0) gi=0;

        var size=gi+cdload;
        if (size>this.limit) size=this.limit;

        for (var j=gi; j<size; j++)
            if ((!this.rowsCol[j])||(this.rowsCol[j]._rLoad)||(this.rowsCol[j]._sRow)) {
                if (this.rowsBuffer[1][j])
                {
                    this._addFromBufferSR(j);
                }
                else
                {

                  { count=size-gi; start=gi; }
                  this._dl_start[start]=[gi-start,size-gi];
                  var loader = new dtmlXMLLoaderObject(this._askRealRows2,this);

			      if (afterCall) loader.waitCall=afterCall;
                  loader.loadXML(this._dload+((this._dload.indexOf("?")!=-1)?"&":"?")+"posStart="+start+"&count="+count+"&sn="+(new Date()).valueOf());
                  if (this.onXLS) this.onXLS(this);
                  return;
                }
             }
    }


     dhtmlXGridObject.prototype._askRealRows2=function(obj,xml,c,d,e){
        var top=e.getXMLTopNode("rows");
		var inmd=obj._initDrF;

        if (inmd) {

            }

        var rows=e.doXPath("//rows/row",top);
		var z_t=top.getAttribute("total_count");
        if ((z_t)&&(!obj._limitC)){
			obj._limitC=obj.limit=parseInt(z_t);
//			obj._fastAddRowSpacer(0,obj.limit*obj._srdh);
			}


        if (inmd) obj._initD();



        var j=parseInt(top.getAttribute("pos"))||0;
        var llim=(obj._dl_start[j]||[0])[0];
        var tlim=llim+(obj._dl_start[j]||[0,rows.length])[1];



        for (var i=0; i<rows.length; i++){

            {
              if ((!obj.rowsCol[i+j])||(obj.rowsCol[i+j]._sRow))
                  obj._splitRowAt(i+j);

              if (obj.rowsCol[i+j]._rLoad){
                  //set value
                if (obj._cssEven){
                  if ((j+i)%2==1) obj.rowsCol[i+j].className=obj._cssUnEven;
                  else obj.rowsCol[i+j].className=obj._cssEven;
                }
                  obj.changeRowId(obj.rowsCol[i+j].idd,rows[i].getAttribute("id"));				
                  obj._fillRowFromXML(obj.rowsCol[i+j],rows[i],-1);
                  obj.rowsCol[i+j]._rLoad=false;
              }
            }
        }

        if (obj.onXLE) obj.onXLE(this,tlim-llim);
    }
/**
*   @desc:  split fake row, to create a real one
*   @type:  private
*/
     dhtmlXGridObject.prototype._splitRowAt=function(ind){
        var id='temp_dLoad_'+this._dInc;
        this._dInc++;
        var z=this.rowsCol[ind];
        if (!z)
        {
            //middle
            var ind2=this._findSParent(ind);


            var delta=ind2[1]-(ind-ind2[0])*this._srdh;
            this._fixHeight(this.rowsCol[ind2[0]],delta);


            var z2=this._fastAddRow(id,ind,true,ind2[0])
            z2._sRow=true;

            this._fixHeight(z2,-1*((ind2[1]-(ind-ind2[0])*this._srdh)-this._srdh));
            return this._splitRowAt(ind);
        }
        else
        if (z._sRow){
            //first
            if ((this.rowsBuffer[1][ind])&&(this.rowsBuffer[1][ind].tagName=="TR"))
                (this._fastAddRow(id,ind,true,null,this.rowsBuffer[1][ind]))._rLoad=false;
            else
                (this._fastAddRow(id,ind,true))._rLoad=true;
                
            if ((!z.style.height)||(parseInt(z.style.height)==this._srdh))
            	z.parentNode.removeChild(z);
        	else{
            	this.rowsCol[ind+1]=z;
            	this._fixHeight(z,this._srdh);
            }
            if (ind==0) this.setSizes();
        }

    }
/**
*   @desc:  find a fake row, related to index in question
*   @type:  private
*/
     dhtmlXGridObject.prototype._findSParent=function(ind){
        for (var i=ind-1; i>=0; i--){
            if (this.rowsCol[i]) {
            return [i,(parseInt(this.rowsCol[i].style.height))];
            }
            }
    }
/**
*   @desc:  change height of fake row
*   @type:  private
*/
     dhtmlXGridObject.prototype._fixHeight=function(z,delta){
        var x=parseInt(z.style.height||this._srdh)-delta;
        if (x==this._srdh) { z._sRow=false; z._rLoad=true; }

        z.style.height=x+"px";
        var n=z.childNodes.length;
        for (var i=0; i<n; i++)
            z.childNodes[i].style.height=x+"px";
    }
/**
*   @desc:  add a fake row
*   @type:  private
*/
     dhtmlXGridObject.prototype._fastAddRowSpacer=function(ind,height){ 
         var id='temp_dLoad_'+this._dInc;
         this._dInc++;

        var z=this._fastAddRow(id,ind);
        z.style.height=height+"px";
        var n=z.childNodes.length;
        for (var i=0; i<n; i++)
            z.childNodes[i].style.height=height+"px";

        z._sRow=true;
    }


/**
*   @desc:  add a row placeholder ( nearly same as real row but without data )
*   @type:  private
*/
     dhtmlXGridObject.prototype._fastAddRow=function(id,ind,nonshift,ind2,z){ 	
        var z=z||this._prepareRow(id);


        if (((ind2)||(ind2=="0"))&&(this.rowsCol[ind2].nextSibling))
            this.rowsCol[ind2].parentNode.insertBefore(z,this.rowsCol[ind2].nextSibling);
        else
        {
        if ((ind==this.limit)||(this.obj._rowslength()==0)||(!this.rowsCol[ind])){
            if (_isKHTML)
                this.obj.appendChild(z);
            else{
                if (!this.obj.firstChild)
                    this.obj.appendChild(document.createElement("TBODY"));
                this.obj.childNodes[0].appendChild(z);
                }
             }
        else
            this.rowsCol[ind2||ind].parentNode.insertBefore(z,this.rowsCol[ind]);
        }


        this.rowsAr[id] = z;
        if (!nonshift)
            this.rowsCol._dhx_insertAt(ind,z);
        else
            this.rowsCol[ind]=z;

        return z;
    };

 	/**
	*	@desc: gets a list of all row ids in grid
	*	@param: separator - delimiter to use in list
	*	@returns: list of all row ids in grid
	*	@type: public
	*	@topic: 2,7
	*/
dhtmlXGridObject.prototype.getAllItemIds = function(separator){
							var ar = new Array(0)
                            var z=this.getRowsNum();
							for(i=0;i<z;i++)
                                if ((this.rowsCol[i])&&(!this.rowsCol[i]._sRow)&&(!this.rowsCol[i]._rLoad))
                                   ar[ar.length]=this.rowsCol[i].idd;
                                else if (this.rowsBuffer[1][i])
                                   ar[ar.length]=this.rowsBuffer[0][i];

							return ar.join(separator||",")
						}

/**
*   @desc:  replace original add row functionality in Smart rendering mode
*   @type:  private
*/
dhtmlXGridObject.prototype._insertRowAt_WSRDA = function(r,ind,skip){
                            if (ind<0) ind=this.rowsBuffer[0].length;
							if ((arguments.length<2)||(ind===window.undefined))
								ind = this.rowsBuffer[0].length//getRowsNum();
							else{
								if(ind>this.rowsBuffer[0].length)
									ind = this.rowsBuffer[0].length;
							}

                            var ind2=this.rowsBuffer[0][ind]||(this.rowsCol[ind]?this.rowsCol[ind].idd:null);
                            this.getRowById(ind2); //draw


                            if (!skip)
                            if (ind==this.rowsBuffer[0].length){
                                if (_isKHTML)
                                    this.obj.appendChild(r);
                                else{
                                    this.obj.firstChild.appendChild(r);
                                    }
                                this.rowsBuffer[0][ind]=r.idd;
                                this.rowsBuffer[1][ind]=null;
                                ind2=ind;
                                }
                            else
                                {
                                if (!this.rowsCol[ind])
                                    ind2=(this._findSParent(ind)[0]);
                                else ind2=ind;
                                this.rowsCol[ind2].parentNode.insertBefore(r,this.rowsCol[ind2]);
                                this.rowsBuffer[0]._dhx_insertAt(ind,r.idd);
                                this.rowsBuffer[1]._dhx_insertAt(ind,null);
                                }

                            this.limit+=1;
							this.rowsAr[r.idd] = r;
							this.rowsCol._dhx_insertAt(ind2,r);

                            if (this._cssEven){
                                if (ind%2==1) r.className+=" "+this._cssUnEven;
                                else r.className+=" "+this._cssEven;

                                if (ind!=(this.rowsCol.length-1))
                                    this._fixAlterCss(ind+1);
                            }

						   	this.doOnRowAdded(r);
                            if ((this.math_req)&&(!this._parsing_)){
                                for(var i=0;i<this.hdr.rows[0].cells.length;i++)
                                   this._checkSCL(r.childNodes[i]);
                                this.math_req=false;
                            }
                            return r;
    }
/**
*   @desc:  replace original delete row functionality in Smart rendering mode
*   @type:  private
*/
dhtmlXGridObject.prototype.deleteRow_WSRDA = function(row_id,node){
                                var ind=-1;
                                var fixind=null;
                                if (this.rowsAr[row_id]){
                                    ind=this.rowsCol._dhx_find(this.rowsAr[row_id]);
									if (this.deleteRow_WSRD(row_id,node)==false) return false;
                                }
                                if (ind<0){
                                    var ind=this.rowsBuffer[0]._dhx_find(row_id);
                                    if (ind>-1) fixind=this.rowsCol[this._findSParent(ind)[0]];
                                    }

                                if (ind>-1)
                                {
                                    this.rowsBuffer[0]._dhx_delAt(ind);
                                    this.rowsBuffer[1]._dhx_delAt(ind);
                                    this.limit-=1;
                                    if (fixind) this._fixHeight(fixind,this._srdh);
                                }
                            return true;
						}

