<?php
function date_sort($d1, $d2)
{
	list($y1, $m1) = explode('-', $d1);
	list($y2, $m2) = explode('-', $d2);

	if($y1 < $y2)
		return -1;
	else if($y1 > $y2)
		return 1;
	else if($m1 < $m2)
		return -1;
	else if($m1 > $m2)
		return 1;
	else // everything is equal
		return 0;
}
?>
