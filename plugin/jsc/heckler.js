! function ( $ )
{
  var htbl = document.getElementById( 'hook_list' )
  var list = htbl.getElementsByTagName( 'tbody' )[ 0 ]

  var rule = editor( document.getElementById( 'code_rule' ) , 'rule' )
  var code = editor( document.getElementById( 'code_code' ) , 'code' )

  var opts =
    { stop : ( e , u ) => { renumb( list ) }
    , items : '.hook_item'
    }

  $( document ).on( 'click' , '#hook_list tbody .action' , remove )
  $( document ).on( 'click' , '#hook_list tfoot .action' , create )

  $( '#hook_list tbody' ).sortable( opts )

  function remove ( $evnt )
  {
    $( this ).closest( 'tr' ).remove()
    renumb( list )
  }

  function create ( $evnt )
  {
    $( this ).closest( 'tr' ).clone().appendTo( list )
    $( this ).closest( 'tr' ).find( 'input' ).val( '' )
    renumb( list )
  }

  function editor ( $elem , $type )
  {
    var vimm = cookie( $type + '_vim' ) === 'true'

    var opts =
      { mode : 'text/x-php'
      , lineNumbers : true
      , firstLineNumber : 2
      , keyMap : vimm ? 'vim' : 'default'
      }

    var check = document.getElementById( 'vim-' + $type )
        check.checked = vimm

    var editor = wp.CodeMirror.fromTextArea( $elem , opts )

    var toggler = function ( e )
    {
      if ( this.checked )
      {
        editor.setOption( 'keyMap' , 'vim' )
        cookie( $type + '_vim' , 'true' )
      }
      else
      {
        editor.setOption( 'keyMap' , 'default' )
        cookie( $type + '_vim' , 'false' )
      }
    }

    check.addEventListener( 'change' , toggler )

    setTimeout( editor.refresh , 5000 )

    editor.refresh()

    return editor
  }

  function renumb ( $htbl )
  {
    var rows = $htbl.getElementsByClassName( 'hook_item' )
        rows = !rows ? [] : Array.from( rows )

    for ( var i = 0 ; i < rows.length ; i++ )
    {
      var inputs = rows[ i ].getElementsByTagName( 'input' )
          inputs = !inputs ? [] : Array.from( inputs )

      for ( var j = 0 ; j < inputs.length ; j++ )
      {
        var name = inputs[ j ].name
            name = name.replace( /hook_list\[(.+?)\].*/ , ( $0 , $1 ) => $0.replace( 'hook_list[' + $1 + ']' , 'hook_list[' + i + ']' ) )

        inputs[ j ].name = name
      }
    }
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