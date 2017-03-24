<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>mIRC logs</title>
</head>
<body>
<?php
setlocale(LC_TIME, 'en_EN');
$files = glob('*.log');
foreach ($files as $k => $file) {
    $logs[$k]['file'] = $file;
    preg_match('/([0-9]{4})([0-9]{2})([0-9]{2}).log/', $file, $date);
    $logs[$k]['date'] = strtotime($date[1] . '-' . $date[2] . '-' . $date[3]);
    $logs[$k]['html'] = str_replace('#', '', basename($file, '.log') . '.html');
}
usort($logs, 'sortByDates');
?>
<div style="text-align: center">
    <ul>
        <?php
        $i = 1;
        foreach ($logs as $log) {
            $jour[$i] = $log['date'];
            if ($i == 1 || $jour[$i] < $jour[$i - 1]) {
                ?>
                <h2><?= strftime('%A %e %B %Y', $jour[$i]) ?></h2>
                <?php
            }
            $contenu = file_get_contents($log['file']);
            $contenu = mircstyles($contenu);
            $html = $log['html'];
            $before = '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>' . $log['file'] . '</title></head><body>';
            $after = '</body></html>';
            file_put_contents($html, $before . $contenu . $after);
            ?>
            <li><a href="<?= "$html" ?>"><?= "{$log['file']}" ?></a></li>
            <?php
            $i++;
        }
        ?>
    </ul>
</div>
</body>
</html>

<?php
/**
 * To generate styled span tags
 *
 * @param $masque array
 * @return string
 */
function colors($masque)
{
    $colors = array(
        'white', 'black', '#00007f', '#009300', 'red', '#7f0000', '#9c009c', '#fc7f00',
        'yellow', '#00fc00', '#009393', '#00ffff', '#0000fc', '#ff00ff', '#7f7f7f', '#d2d2d2'
    );

    $return = '<span style="color:' . $colors[(int)$masque[1]] . ';';
    if ($masque[2]) {
        $return .= 'background-color:' . $colors[(int)$masque[3]] . ';';
    }
    $return .= '">' . $masque[4] . '</span>';

    return $return;
}

/**
 * This is the main function which uses regex and
 * call function colors() to generate styled span tags
 *
 * @param $text
 * @return string
 */
function mircstyles($text)
{
    $bold = '';
    $underline = '';
    $reverse = '';
    $color = '';
    $unstyle = '';

    $expreg = array(
        '/' . $bold . '([^' . $bold . $unstyle . ']*)[' . $bold . ']?[' . $unstyle . ']?/',
        '/' . $underline . '([^' . $underline . $unstyle . ']*)[' . $underline . ']?[' . $unstyle . ']?/',
        '/' . $reverse . '([^' . $reverse . $unstyle . ']*)[' . $reverse . ']?[' . $unstyle . ']?/',
        '/' . $unstyle . '/'
    );

    $replace = array(
        '<b>$1</b>',
        '<u>$1</u>',
        '<span style="background-color:black;color:white;">$1</span>',
        ''
    );

    $strings = [];
    $array = explode("\r\n", $text);
    foreach ($array as $ligne) {
        $ligne = htmlspecialchars($ligne, ENT_QUOTES);
        $string = preg_replace($expreg, $replace, $ligne);
        $string = preg_replace_callback("/$color([0-9]{1,2})(,([0-9]{1,2}))?([^{$color}{$unstyle}]*)/", "colors", $string);
        $strings[] = $string;
    }

    return implode(" <br />\r\n", $strings);
}

/**
 * To sort logs by decreasing dates
 *
 * @param $a
 * @param $b
 * @return int
 */
function sortByDates($a, $b)
{
    if ($a['date'] > $b['date']) return -1;
    elseif ($a['date'] == $b['date']) return strcasecmp($a['file'], $b['file']);
    else return 1;
}

?>