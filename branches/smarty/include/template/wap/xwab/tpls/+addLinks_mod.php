// Are there any additional navlinks we should insert into the array before imploding it?
if ($pun_config['o_additional_navlinks']) {
if (preg_match_all('#([0-9]+)\s*=\s*(.*?)\n#s', $pun_config['o_additional_navlinks'], $extra_links)) {
// Insert any additional links into the $links array (at the correct index)
for ($i = 0, $all = sizeof($extra_links[1]); $i < $all; ++$i) {
if (preg_match('!<a[^>]+href="?\'?([^ "\'>]+)"?\'?[^>]*>([^<>]*?)</a>!is', $extra_links[2][$i], $row)) {
                array_splice($out, $extra_links[1][$i], 0, array('<option value="' . $row[1] . '">' . $row[2] . '
</option>'));
            }
        }
    }
}