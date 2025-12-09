
<?php
$n=0;
$sum=0;
////Dung while 
// while($sum<=1000){
//     $sum+=$n;
//     $n++;
// }

////Dung do ... while
do{
    $n++;
    $sum+=$n;
    echo "n=".$n;
    echo " sum=".$sum;
    echo "<br/>";
}while($sum<1000);
echo "<hr>";
echo "Gia tri n nho nhat: ".$n;
echo "<br/>";
echo"Tong bang: ".$sum;
?> 
