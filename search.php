<?php
// +-----------------------------------------------------------------------+
// |                           search.php                                |
// +-----------------------------------------------------------------------+
// | application   : PhpWebGallery <http://phpwebgallery.net>              |
// | branch        : 1.4                                                   |
// +-----------------------------------------------------------------------+
// | file          : $RCSfile$
// | last update   : $Date$
// | last modifier : $Author$
// | revision      : $Revision$
// +-----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify  |
// | it under the terms of the GNU General Public License as published by  |
// | the Free Software Foundation                                          |
// |                                                                       |
// | This program is distributed in the hope that it will be useful, but   |
// | WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU      |
// | General Public License for more details.                              |
// |                                                                       |
// | You should have received a copy of the GNU General Public License     |
// | along with this program; if not, write to the Free Software           |
// | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, |
// | USA.                                                                  |
// +-----------------------------------------------------------------------+

//----------------------------------------------------------- include
$phpwg_root_path = './';
include_once( $phpwg_root_path.'include/common.inc.php' );
//-------------------------------------------------- access authorization check
check_login_authorization();
//----------------------------------------------------------------- redirection
$error = array();
if ( isset( $_POST['search'] ) )
{
  $redirect = true;
  $search = array();
  $words = preg_split( '/\s+/', $_POST['search'] );
  foreach ( $words as $i => $word ) {
    if ( strlen( $word ) > 2 and !preg_match( '/[,;:\']/', $word ) )
    {
      array_push( $search, $word );
    }
    else
    {
      $redirect = false;
      array_push( $error, $lang['invalid_search'] );
      break;
    }
  }
  $search = array_unique( $search );
  $search = implode( ',', $search );
  if ( $redirect )
  {
    $url = 'category.php?cat=search&search='.$search.'&mode='.$_POST['mode'];
    $url = add_session_id( $url, true );
    header( 'Request-URI: '.$url );
    header( 'Content-Location: '.$url );  
    header( 'Location: '.$url );
    exit();
  }
}
//----------------------------------------------------- template initialization
//
// Start output of page
//
$title= $lang['search_title'];
include('include/page_header.php');

$handle = $vtp->Open( './template/'.$user['template'].'/search.vtp' );
initialize_template();
$tpl = array( 'search_title','search_return_main_page','submit',
              'search_comments' );
templatize_array( $tpl, 'lang', $handle );
//----------------------------------------------------------------- form action
$vtp->setGlobalVar( $handle, 'form_action', add_session_id( './search.php' ) );
//-------------------------------------------------------------- errors display
if ( sizeof( $error ) != 0 )
{
  $vtp->addSession( $handle, 'errors' );
  for ( $i = 0; $i < sizeof( $error ); $i++ )
  {
    $vtp->addSession( $handle, 'li' );
    $vtp->setVar( $handle, 'li.li', $error[$i] );
    $vtp->closeSession( $handle, 'li' );
  }
  $vtp->closeSession( $handle, 'errors' );
}
//------------------------------------------------------------------------ form
// search field
$vtp->addSession( $handle, 'line' );
$vtp->setVar( $handle, 'line.name', $lang['search_field_search'].' *' );
$vtp->addSession( $handle, 'text' );
$vtp->setVar( $handle, 'text.size', '40' );
$vtp->setVar( $handle, 'text.name', 'search' );
if (isset($_POST['search']))
$vtp->setVar( $handle, 'text.value', $_POST['search'] );
$vtp->closeSession( $handle, 'text' );
$vtp->closeSession( $handle, 'line' );
// mode of search : match all words or at least one of this words
$vtp->addSession( $handle, 'line' );
$vtp->addSession( $handle, 'group' );

$vtp->addSession( $handle, 'radio' );
$vtp->setVar( $handle, 'radio.name', 'mode' );
$vtp->setVar( $handle, 'radio.value', 'OR' );
$vtp->setVar( $handle, 'radio.option', $lang['search_mode_or'] );
if (!isset($_POST['mode']) || $_POST['mode'] == 'OR' )
{
  $vtp->setVar( $handle, 'radio.checked', ' checked="checked"' );
}
$vtp->closeSession( $handle, 'radio' );

$vtp->addSession( $handle, 'radio' );
$vtp->setVar( $handle, 'radio.name', 'mode' );
$vtp->setVar( $handle, 'radio.value', 'AND' );
$vtp->setVar( $handle, 'radio.option', $lang['search_mode_and'] );
if ( isset($_POST['mode']) && $_POST['mode'] == 'AND' )
{
  $vtp->setVar( $handle, 'radio.checked', ' checked="checked"' );
}
$vtp->closeSession( $handle, 'radio' );

$vtp->closeSession( $handle, 'group' );
$vtp->closeSession( $handle, 'line' );
//---------------------------------------------------- return to main page link
$vtp->setGlobalVar( $handle, 'back_url', add_session_id( './category.php' ) );
//----------------------------------------------------------- html code display
$code = $vtp->Display( $handle, 0 );
echo $code;
//------------------------------------------------------------ log informations
pwg_log( 'search', $title );
mysql_close();
include('include/page_tail.php');
?>