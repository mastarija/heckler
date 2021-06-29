! function ( $ )
{
  var rule = editor( document.getElementById( 'code_rule' ) )
  var code = editor( document.getElementById( 'code_code' ) )

  function editor ( $elem )
  {
    var opts =
      { mode : 'text/x-php'
      , lineNumbers : true
      , firstLineNumber : 2
      }

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
} ( jQuery )