! function ( $ )
{
  var hook = document.querySelector( '#heckler-hook tbody' )

  editor( document.getElementById( 'heckler-rule-editor' ) )
  editor( document.getElementById( 'heckler-code-editor' ) )

  $( document ).on( 'click' , 'tbody .heckler-hook-kill span' , remove )
  $( document ).on( 'click' , 'tfoot .heckler-hook-kill span' , create )

  function remove ( $evnt )
  {
    $( this ).closest( 'tr' ).remove()
  }

  function create ( $evnt )
  {
    $( this ).closest( 'tr' ).clone().appendTo( hook )
    $( this ).closest( 'tr' ).find( 'input' ).val( '' )
  }

  function editor ( $elem )
  {
    var opts = { mode : 'text/x-php' , keyMap : 'vim' , lineNumbers : true }
    return wp.CodeMirror.fromTextArea( $elem , opts )
  }
} ( jQuery )
