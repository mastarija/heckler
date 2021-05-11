! function ( $ )
{
  var main = document.querySelector( '#heckler .hooks_table tbody' )
  var nrow = document.querySelector( '#heckler .hooks_table tfoot tr' )
  var vals = !nrow ? false : Array.from( nrow.querySelectorAll( 'input' ) )

  if ( !main || !nrow || !vals )
  {
    return
  }

  $( document ).on( 'click' , '#heckler .add' , add_handler )
  $( document ).on( 'click' , '#heckler .del' , del_handler )

  function add_handler ( e )
  {
    var copy = nrow.cloneNode( true )
    var butn = copy.querySelector( '.add' )
        butn.classList.add( 'del' )
        butn.classList.remove( 'add' )

    main.appendChild( copy )
    vals.forEach( f => f.value = '' )
  }

  function del_handler ( e )
  {
    e.preventDefault()
    this.parentNode.parentNode.remove()
  }
} ( jQuery )