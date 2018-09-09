<? echo("<?"); ?>xml version="1.0" encoding="utf-8" <? echo("?>"); ?>
<grammar xmlns="http://www.w3.org/2001/06/grammar" xml:lang="ru-ru" version="1.0" mode="voice" root="root" tag-format="semantics/1.0-literals">
<rule id="root">
<one-of>
<?

$ff = fopen("rab.csv", "r") or die("Ошибка!");

while($dr = fgetcsv($ff, 1000, "\n"))
    {
#    print_r($dr);

    $dt = explode(";", $dr[0]);

    $dd = explode(",", $dt[1]);

#    print_r($dd);

    foreach($dd as $ds)
        {
        ?><item><? echo($ds); ?><tag><? echo($dt[0]); ?></tag></item><? echo("\n");
        }

    }

fclose($ff);

?>
</one-of>
</rule>
</grammar>
