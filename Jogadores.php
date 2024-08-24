<?php include "../inc/dbinfo.inc"; ?>

<html>
<body>
<h1>Página de Futebol</h1>
<?php

/* Conectar ao PostgreSQL e selecionar o banco de dados. */
$constring = "host=" . DB_SERVER . " dbname=" . DB_DATABASE . " user=" . DB_USERNAME . " password=" . DB_PASSWORD ;
$connection = pg_connect($constring);

if (!$connection){
 echo "Falha ao conectar ao PostgreSQL";
 exit;
}

/* Garantir que a tabela JOGADORES existe. */
VerifyPlayersTable($connection, DB_DATABASE);

/* Se os campos de entrada estiverem preenchidos, adicionar uma linha à tabela JOGADORES. */
$player_name = htmlentities($_POST['NAME']);
$player_position = htmlentities($_POST['POSITION']);
$player_age = intval($_POST['AGE']);
$player_is_active = isset($_POST['IS_ACTIVE']) ? 'true' : 'false';

if (strlen($player_name) || strlen($player_position) || $player_age > 0) {
  AddPlayer($connection, $player_name, $player_position, $player_age, $player_is_active);
}

/* Verifica se uma solicitação de exclusão foi feita e executa a exclusão. */
if (isset($_POST['delete_id'])) {
  $delete_id = intval($_POST['delete_id']);
  DeletePlayer($connection, $delete_id);
}

?>

<!-- Formulário de entrada -->
<form action="<?PHP echo $_SERVER['SCRIPT_NAME'] ?>" method="POST">
  <table border="0">
    <tr>
      <td>NOME</td>
      <td>POSIÇÃO</td>
      <td>IDADE</td>
      <td>ATIVO</td>
    </tr>
    <tr>
      <td><input type="text" name="NAME" maxlength="45" size="30" /></td>
      <td><input type="text" name="POSITION" maxlength="45" size="30" /></td>
      <td><input type="number" name="AGE" min="1" max="100" size="10" /></td>
      <td><input type="checkbox" name="IS_ACTIVE" /></td>
      <td><input type="submit" value="Adicionar Jogador" /></td>
    </tr>
  </table>
</form>

<!-- Exibir dados da tabela. -->
<table border="1" cellpadding="2" cellspacing="2">
  <tr>
    <td>ID</td>
    <td>NOME</td>
    <td>POSIÇÃO</td>
    <td>IDADE</td>
    <td>ATIVO</td>
    <td>AÇÃO</td>
  </tr>

<?php

$result = pg_query($connection, "SELECT * FROM JOGADORES");

while($query_data = pg_fetch_row($result)) {
  echo "<tr>";
  echo "<td>",$query_data[0], "</td>",
       "<td>",$query_data[1], "</td>",
       "<td>",$query_data[2], "</td>",
       "<td>",$query_data[3], "</td>",
       "<td>",$query_data[4] == 't' ? 'Sim' : 'Não', "</td>";
  echo "<td>
          <form method='POST' action='".$_SERVER['SCRIPT_NAME']."'>
            <input type='hidden' name='delete_id' value='".$query_data[0]."' />
            <input type='submit' value='Deletar' />
          </form>
        </td>";
  echo "</tr>";
}
?>
</table>

<!-- Limpar recursos. -->
<?php

  pg_free_result($result);
  pg_close($connection);
?>
</body>
</html>


<?php

/* Adicionar um jogador à tabela. */
function AddPlayer($connection, $name, $position, $age, $is_active) {
   $n = pg_escape_string($name);
   $p = pg_escape_string($position);
   $a = intval($age);
   $active = $is_active === 'true' ? 'TRUE' : 'FALSE';

   $query = "INSERT INTO JOGADORES (NAME, POSITION, AGE, IS_ACTIVE) VALUES ('$n', '$p', $a, $active);";

   if(!pg_query($connection, $query)) echo("<p>Erro ao adicionar dados do jogador.</p>"); 
}

/* Excluir um jogador da tabela. */
function DeletePlayer($connection, $id) {
   $id = intval($id);
   $query = "DELETE FROM JOGADORES WHERE ID = $id;";

   if(!pg_query($connection, $query)) echo("<p>Erro ao excluir o jogador.</p>"); 
}

/* Verificar se a tabela existe e, se não, criá-la. */
function VerifyPlayersTable($connection, $dbName) {
  if(!TableExists("JOGADORES", $connection, $dbName))
  {
     $query = "CREATE TABLE JOGADORES (
         ID serial PRIMARY KEY,
         NAME VARCHAR(45),
         POSITION VARCHAR(45),
         AGE INT,
         IS_ACTIVE BOOLEAN
       )";

     if(!pg_query($connection, $query)) echo("<p>Erro ao criar tabela.</p>"); 
  }
}

/* Verificar a existência de uma tabela. */
function TableExists($tableName, $connection, $dbName) {
  $t = strtolower(pg_escape_string($tableName));
  $query = "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_NAME = '$t';";
  $checktable = pg_query($connection, $query);

  if (pg_num_rows($checktable) > 0) return true;
  return false;

}
?>
