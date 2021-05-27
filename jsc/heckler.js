! function ( $ )
{
  var hook = document.querySelector( '#heckler-hook tbody' )
  var vimm = cookie( 'vimode' )

  var rule = editor( document.getElementById( 'heckler-rule-editor' ) )
  var code = editor( document.getElementById( 'heckler-code-editor' ) )
  var cvim = $( '#heckler-mode-cvim' ).click( vimode )

  if ( vimm === 'true' )
  {
    rule.setOption( 'keyMap' , 'vim' )
    code.setOption( 'keyMap' , 'vim' )
    cvim.prop( 'checked' , true )
  }

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
    var opts = { mode : 'text/x-php' , keyMap : 'default' , lineNumbers : true }
    return wp.CodeMirror.fromTextArea( $elem , opts )
  }

  function cookie ( $name , $data , $date )
  {
    if ( !$name )
    {
      return
    }

    if ( !$data )
    {
      var reg = new RegExp( $name + '=([^;]+)' )
      var val = reg.exec( document.cookie )
      return val ? unescape( val[ 1 ] ) : null
    }

    document.cookie = $name + '=' + $data + ( $date ? '; expires=' + $date.toGMTString() : '' ) + '; path=/'

    return $data
  }

  function vimode ( )
  {
    if ( $( this ).is( ':checked' ) )
    {
      console.log( 'checked' )
      cookie( 'vimode' , 'true' )
      rule.setOption( 'keyMap' , 'vim' )
      code.setOption( 'keyMap' , 'vim' )
    }
    else
    {
      console.log( 'unchecked' )
      cookie( 'vimode' , 'false' )
      rule.setOption( 'keyMap' , 'default' )
      code.setOption( 'keyMap' , 'default' )
    }
  }
} ( jQuery )
