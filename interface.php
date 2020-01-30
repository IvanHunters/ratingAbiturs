 <?php
include "kernel.php";
$db = new database();
$vuzs = $db->execute_query("SELECT * FROM vuzs WHERE vuz !='volsu'",true);
?>
<h1>Выберите ВУЗ</h1>
<form action="info_vuz.php" method="post">
    <p><select name="vuz">
            <option disabled>Выберите ВУЗ</option>
            <?php while($row = $vuzs->fetch_assoc()):?>
            <option value="<?=$row['vuz']?>"><?=$row['description']?></option>
            <?php endwhile;?>
        </select></p>
        <p><select name="system_of_preparation">
                <option selected value='bs'>Бакалавриат + Специалитет</option>
                <option value='магистратура'>Магистратура</option>
            </select></p>
    <p><input type="submit" value="Получить информацию"></p>
</form>
