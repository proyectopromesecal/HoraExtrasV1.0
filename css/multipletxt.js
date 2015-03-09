var num = 0; 
evento = function (evt) { 
   return (!evt) ? event : evt;
}
agregaCampoTxt = function () { 
   div = document.createElement('div');
   div.className = 'text';
   div.id = 'text' + (++num);
   campo = document.createElement('input');
   campo.name = 'txtAdress[]';
   campo.type = 'text';
   campo.value = '@promesecal.gob.do';
   a = document.createElement('a');
   a.name = div.id;
   a.href = '#';
   a.onclick = borraCampo;
   a.innerHTML = '      Eliminar';
   div.appendChild(campo);
   div.appendChild(a);
   contenedor = document.getElementById('multptxt');
   contenedor.appendChild(div);
}
borraCampo = function (evt){
   evt = evento(evt);
   campo = remObj(evt);
   div = document.getElementById(campo.name);
   div.parentNode.removeChild(div);
}	
remObj = function (evt) { 
   return evt.srcElement ?  evt.srcElement : evt.target;
}