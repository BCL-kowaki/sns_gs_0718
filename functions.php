<?php

function connect_to_db()
{
  $dbn = 'mysql:dbname=LAA1573058-gbvohu;charset=utf8mb4;port=3306;host=mysql302.phy.lolipop.lan';
  $user = 'LAA1573058';
  $pwd = 'f4vfSu5a3pxnxXkC';
    try {
      return new PDO($dbn, $user, $pwd);
    } catch (PDOException $e) {
      exit('dbError:'.$e->getMessage());
    }


// function connect_to_db()
// {
//     $dbn='mysql:dbname=gs_d15_06;charset=utf8mb4;port=3306;host=localhost';
//     $user = 'root';
//     $pwd = '';
//     try {
//       return new PDO($dbn, $user, $pwd);
//     } catch (PDOException $e) {
//       exit('dbError:'.$e->getMessage());
//     }
}