<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path());
loggedinorreturn();
parked();
if (get_user_class() < $viewuserlist_class)
permissiondenied();
$search = trim($_GET['search']);
$class = $_GET['class'];
$country = 0+$_GET['country'];
$letter = trim($_GET["letter"]);

if (strlen($letter) > 1)
	die;

if(!is_valid_user_class($class))
	$class = '-';

if (($search != '' || $class != '-') && $letter == '')
{
	$query = "username LIKE " . sqlesc("%$search%") . " AND status='confirmed'";
	if ($search)
		$q = "search=" . rawurlencode($search);
}
elseif ($letter != '' && strpos("0abcdefghijklmnopqrstuvwxyz", $letter) == true)
{
  $query = "username LIKE '$letter%' AND status='confirmed'";
  $q = "letter=$letter";
}
else
{
$query = "status='confirmed'";
$q = '';
}

if ($class != '-')
{
	$query .= " AND class=$class";
	$q .= ($q ? "&" : "") . "class=$class";
}

if ($country > 0)
{
	$query .= " AND country=$country";
	$q .= ($q ? "&" : "") . "country=$country";
}
stdhead($lang_users['head_users']);
begin_main_frame($lang_users['text_users'], true);

print("<form method=get action=?>\n");
print($lang_users['text_search'] ." <input type=text style=\"width:100px\" name=search value=$search> \n");
print("<select name=class>\n");
print("<option value='-'>".$lang_users['select_any_class']."</option>\n");
for ($i = 0;;++$i)
{
	if ($c = get_user_class_name($i,false,true,true))
		print("<option value=$i" . ($class != '-' && $class == $i ? " selected" : "") . ">$c</option>\n");
	else
		break;
}
print("</select>\n");
$countries = "<option value=0>".$lang_users['select_any_country']."</option>\n";
$ct_r = sql_query("SELECT id,name FROM countries ORDER BY name") or die;
while ($ct_a = _mysql_fetch_array($ct_r))
	$countries .= "<option value=".htmlspecialchars($ct_a['id']).">".htmlspecialchars($ct_a['name'])."</option>\n";
print("<select name=country>".$countries."</select>");
print("<input type=submit value=\"".$lang_users['submit_okay']."\">\n");
print("</form>\n");

$href = ($country > 0 ? "&country=".$country : "");
if($class != '-') {
  $href .= '&class=' . $class;
}

echo a_to_z_index($letter, $href);
end_main_frame();

$perpage = 50;

$res = sql_query("SELECT COUNT(*) FROM users WHERE $query") or sqlerr();
$arr = _mysql_fetch_row($res);
$count = $arr[0];

list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "users.php?".$q.($q ? "&" : ""));


$country_sql = "concat('<img src=\"pic/flag/', countries.flagpic, '\" alt=\"', countries.name  ,'\">')";

$sql = sprintf('SELECT
        users.id     as  id,
        users.class   as   class,
       IF (
         users.country >0, %s, \'---\'
       ) as country,
       IF (
         users.added = "0000-00-00 00:00:00", "-", users.added
       ) as added,
       IF (
         users.last_access = "0000-00-00 00:00:00", "-", users.last_access
       ) as last_access
       
       
       FROM users
       LEFT JOIN countries ON users.country = countries.id
       WHERE %s
       ORDER BY username %s',
       $country_sql, $query, $limit);


$res = sql_query($sql) or sqlerr();


$num = _mysql_num_rows($res);

if ($num == 0) {
  print('<h3 class="page-titles">' . $lang_users['text_no_found'] . '</h3>');
}
else {
  print($pagertop);
  print("<table border=1 cellspacing=0 cellpadding=5>\n");
  print("<tr><td class=colhead align=left>".$lang_users['col_user_name']."</td><td class=colhead>".$lang_users['col_registered']."</td><td class=colhead>".$lang_users['col_last_access']."</td><td class=colhead align=left>".$lang_users['col_class']."</td><td class=colhead>".$lang_users['col_country']."</td></tr>\n");
  for ($i = 0; $i < $num; ++$i)
    {
      $arr = _mysql_fetch_assoc($res);

      print("<tr><td align=left>".get_username($arr['id'])."</td><td>".gettime($arr['added'], true, false)."</td><td>".gettime($arr['last_access'],true,false)."</td><td align=left>". get_user_class_name($arr['class'],false,true,true) . "</td><td align=center>".$arr['country']."</td></tr>");
    }

  print("</table>");
  print($pagerbottom);
}

stdfoot();
die;
?>
