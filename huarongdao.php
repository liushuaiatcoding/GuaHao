<?php
$processingStateArray = array();
$outsideStateArray = array();
$stateNum = 0;
$initStateHard = array(
		"zhangfei" => array(0,0,2,1,'Z','1'), // 分别代表：起始横坐标，纵坐标，横向宽度，纵向宽度，区分棋子名称，唯一key
		"caocao" => array(0,1,2,2,'C','4'),
		"zhaoyun" => array(0,3,2,1,'Y','1'),
		"machao" => array(2,0,2,1,'M','1'),
		"guanyu" => array(2,1,1,2,'G','3'),
		"huangzhong" => array(2,3,2,1,'H','1'),
		"zu1" => array(3,1,1,1,'a','2'),
		"zu2" => array(3,2,1,1,'b','2'),
		"zu3" => array(4,0,1,1,'c','2'),
		"zu4" => array(4,3,1,1,'d','2')
);

$initStateEasy = array(
		"zhangfei" => array(0,0,2,1,'Z','1'),
		"caocao" => array(3,0,2,2,'C','4'),
		"zhaoyun" => array(0,1,2,1,'Y','1'),
		"machao" => array(0,2,2,1,'M','1'),
		"guanyu" => array(2,2,1,2,'G','3'),
		"huangzhong" => array(0,3,2,1,'H','1'),
		"zu1" => array(4,2,1,1,'a','2'),
		// 		"zu1" => array(4,1,1,1,'a'),
		"zu2" => array(4,3,1,1,'b','2'),
		"zu3" => array(2,0,1,1,'c','2'),
		// 		"zu3" => array(4,1,1,1,'c'),
		"zu4" => array(2,1,1,1,'d','2')
);
xhprof_enable();
putToProcessingQueue($initStateHard, 0);
while(count($processingStateArray) > 0) {
	$currentState = array_shift($processingStateArray);
	echo "status num = ", $currentState["stateNum"], " fatherStateNum = ", $currentState["fatherStateNum"], "\n";
	if (isEndState($currentState["status"])) {
		visualizeQipan($currentState["status"]);
		echo "find the huarongdao solution and successful state num is ", $currentState["stateNum"], "\n";
		findTheSolutionPath($currentState);
		break ;
	} else if (isExistingStateInOutsideQueue($currentState)) {
		continue;
	} else {
		$currentStateDetail = $currentState["status"];

		// 构造当前局面
		$forPrintArray = array();
		foreach ($currentStateDetail as $qiziNameTmp => $qiziZuobiaoTmp) {
			for ($i=0; $i<$qiziZuobiaoTmp[2]; $i++) {
				for ($j=0; $j<$qiziZuobiaoTmp[3]; $j++) {
					$forPrintArray[$qiziZuobiaoTmp[0]+$i][$qiziZuobiaoTmp[1]+$j] = $qiziZuobiaoTmp[4];
				}
			}
		}
		
		foreach($currentStateDetail as $name => $zuobiao) { // 针对每一个棋子进行一次四个方向的移动，将合法的状态压存起来
		    validAndPushToProcessingQueue($currentStateDetail, $name, $currentState["stateNum"], $forPrintArray);
		}
	}
	echo "total state in processing queue is : ", count($processingStateArray), "\n";
	echo "----------------------------------------------", "\n";
}

$xhprof_data = xhprof_disable();
$XHPROF_ROOT = "/opt/www/xhprof.traffic.meituan.com/xhprof";                                                                                                    
include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";                                                                                                 
include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";                                                                                                
$xhprof_runs = new XHProfRuns_Default();                                                                                                                        
$run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_foo");                                                                                                                                                                                                                                                                
echo "---------------\n".                                                                                                                                            
"Assuming you have set up the http based UI for \n".                                                                                                                 
"XHProf at some address, you can view run at \n".                                                                                                                    
"http://xhprof.traffic.meituan.com/index.php?run=$run_id&source=xhprof_foo\n".                                                                    
"---------------\n";                                                                                                                        

function findTheSolutionPath($currentState) {
	$totalFindingNum = 0;
	global $outsideStateArray;
    echo "whole different state num is ". count($outsideStateArray). "\n";
    visualizeQipan($currentState["status"]);
    $fatherStateNum = $currentState["fatherStateNum"];
	while ($fatherStateNum != 0) {
		$totalFindingNum++;
		echo "---->".$fatherStateNum."\n";
		$fatherState = findStateByStateNum($fatherStateNum);
		visualizeQipan($fatherState["status"]);
		$fatherStateNum = $fatherState["fatherStateNum"];
	}
	echo "Total spend ",$totalFindingNum, " times to find the final state\n";
}

function findStateByStateNum($stateNum) { // 根据任意一个状态id找到这个状态
	global $outsideStateArray;
	foreach($outsideStateArray as $singleState) {
		if ($singleState["stateNum"] == $stateNum) {
			return $singleState;
		}
	}
}

function putToProcessingQueue($state, $fatherStateNum) {
	global $processingStateArray;
	global $stateNum;
	$currentStateHash = array();
	$currentStateHash["stateNum"] = $stateNum;
	$currentStateHash["fatherStateNum"] = $fatherStateNum;
	$currentStateHash["status"] = $state;
	$processingStateArray[] = $currentStateHash;
	$stateNum++;
}

function isEndState($state) {
	if ($state["caocao"][0] ==3 && $state["caocao"][1]== 1) return true;
	return false;
}

function isExistingStateInOutsideQueue($tempState) {
	global $outsideStateArray;
        if (isset($outsideStateArray[keyOfState($tempState["status"])])) {
        	    return true;
        }
	// 如果不存在，则加入到见过的状态序列
	$outsideStateArray[keyOfState($tempState["status"])] = $tempState;
	return false;
}

function keyOfState($currentStateDetail) { // 根据一个状态找到对应的hashKey，这是为了极大的加速状态匹配	
	// 构造当前局面
	$array20 = array_fill(0,20,0);
	foreach ($currentStateDetail as $qiziNameTmp => $qiziZuobiaoTmp) {
		for ($i=0; $i<$qiziZuobiaoTmp[2]; $i++) {
			for ($j=0; $j<$qiziZuobiaoTmp[3]; $j++) {
				$array20[($qiziZuobiaoTmp[0]+$i)*4+$qiziZuobiaoTmp[1]+$j] = $qiziZuobiaoTmp[5]; //// 存放唯一key
			}
		}
	}
	return implode($array20);
}



function isEqualTwoArray($array1, $array2) {
	for ($i=0; $i<count($array1)-1; $i++) { // 不判断最后一个位置，因为那个是代表元素画图用的
		if ($array1[$i] != $array2[$i]) return false;
	}
	return true;
}

function validAndPushToProcessingQueue($state, $qiziName, $fatherStateNum, $forPrintArray) {
	$qiziZuobiao = $state[$qiziName];
	$tempState = validRight($state, $qiziName, $qiziZuobiao, $forPrintArray);
	if ($tempState != null) {
		putToProcessingQueue($tempState, $fatherStateNum);
		if ($qiziName ==="caocao" ) { echo "caocao right","\n";visualizeQipan($tempState);}
	}
	
	$tempState = validLeft($state, $qiziName, $qiziZuobiao, $forPrintArray);
	if ($tempState != null)  {
		putToProcessingQueue($tempState, $fatherStateNum); 
		if ($qiziName ==="caocao" ) { echo "caocao left","\n";visualizeQipan($tempState);}
	}
	
	$tempState = validDown($state, $qiziName, $qiziZuobiao, $forPrintArray);
	if ($tempState != null)  {
		putToProcessingQueue($tempState, $fatherStateNum); 
		if ($qiziName ==="caocao" ) { echo "caocao down","\n";visualizeQipan($tempState);}
	}
	
	$tempState = validTop($state, $qiziName, $qiziZuobiao, $forPrintArray);
	if ($tempState != null)  {
		putToProcessingQueue($tempState, $fatherStateNum); 
		if ($qiziName ==="caocao" ) { echo "caocao top","\n";visualizeQipan($tempState);}
	}
}
    
function validRight($state, $qiziName, $qiziZuobiao, $statusArray) {
	if ($qiziZuobiao[1]+$qiziZuobiao[3] == 4) {// 越界
		return null;
	}
	for ($i=0; $i<$qiziZuobiao[2]; $i++) {
		if (!empty($statusArray[$qiziZuobiao[0]+$i][$qiziZuobiao[1]+$qiziZuobiao[3]])) {//和其他棋子有交集
			return null;
		}
	}
    $newState = arrayDeepCopy($state);
    $newState[$qiziName][1] = $newState[$qiziName][1]+1;
    return $newState;
}

function validLeft($state, $qiziName, $qiziZuobiao, $statusArray) {
	if ($qiziZuobiao[1]-1 == -1) { // 越界，判定水平方向不越界，也即Y方向往左
		return null;
	}
	for ($i=0; $i<$qiziZuobiao[2]; $i++) { //和其他棋子有交集
		if (!empty($statusArray[$qiziZuobiao[0]+$i][$qiziZuobiao[1]-1])) {
			return null;
		}
	}
	$newState = arrayDeepCopy($state);
	$newState[$qiziName][1] = $newState[$qiziName][1]-1;
	return $newState;
}

function validDown($state, $qiziName, $qiziZuobiao, $statusArray) {
	if ($qiziZuobiao[0]+$qiziZuobiao[2] == 5) {// 越界
		return null;
	}
	for ($j=0; $j<$qiziZuobiao[3]; $j++) {
		if (!empty($statusArray[$qiziZuobiao[0]+$qiziZuobiao[2]][$qiziZuobiao[1]+$j])) {//和其他下方棋子有交集
		    return null;
		}
	}
	$newState = arrayDeepCopy($state);
	$newState[$qiziName][0] = $newState[$qiziName][0]+1;
	return $newState;
}

function validTop($state, $qiziName, $qiziZuobiao, $statusArray) {
	// valid up
	if ($qiziZuobiao[0]-1 == -1) {// 越界
		return null;
	}
	for ($j=0; $j<$qiziZuobiao[3]; $j++) {//和其他上方有交集
		if (!empty($statusArray[$qiziZuobiao[0]-1][$qiziZuobiao[1]+$j])) {
			return null;
		}
	}
	$newState = arrayDeepCopy($state);
	$newState[$qiziName][0] = $newState[$qiziName][0]-1;
	return $newState;
}

function arrayDeepCopy($state) {
	$newState = array();
	foreach($state as $key => $value) {
		$newState[$key] = $state[$key];
	}
	return $state;
}


function visualizeQipan($state) {
	if ($state == null) return;
	$forPrintArray = array();
	foreach ($state as $qiziName => $qiziZuobiao) {
		for ($i=0; $i<($qiziZuobiao[2]); $i++) {
			for ($j=0; $j<($qiziZuobiao[3]); $j++) {
				$forPrintArray[$qiziZuobiao[0]+$i][$qiziZuobiao[1]+$j] = $qiziZuobiao[4];
			}
		}
	}
	for ($i=0; $i<5; $i++) {
		for ($j=0; $j<4; $j++) {
			if (!empty($forPrintArray[$i][$j])) echo $forPrintArray[$i][$j];
			else echo ' ';
		}
		echo "\n";
	}
	echo "====\n";
}

// function equalState($state1, $state2) { // TODO 这个可以优化从而大幅度剪枝。不做剪枝一个小时都跑不出来，12.4 profile证明这个函数占据了绝大部分执行时间
// 	foreach ($state1 as $name => $zuobiao) {
// 		// 等价状态检查，只要发现跟自己长度一样的棋子和自己在同一个位置上，针对这个棋子的判断就算一样，这里判断竖着的棋子
// 		$dengjiaState1 = array("zhangfei", "zhaoyun", "machao", "huangzhong"); // 这里的硬编码需要改成根据横竖长度自动match
// 		$dengjiaState2 = array("zu1", "zu2", "zu3", "zu4");
// 		if (in_array($name, $dengjiaState1)) {
// 			$equalStatus = false;
// 			foreach ($dengjiaState1 as $dengjiaName1) {
// 				if (isEqualTwoArray($zuobiao, $state2[$dengjiaName1])) { //与某一个等价类一样，则标记为一样，并退出本棋子的比较
// 					$equalStatus = true;
// 				    break;
// 				}
// 			}
// 			if (!$equalStatus) { return false; }
// 		} else if (in_array($name, $dengjiaState2)) { // 兵在等价位置
// 			$equalStatus2 = false;
// 			foreach ($dengjiaState2 as $dengjiaName2) {
// 				if (isEqualTwoArray($zuobiao, $state2[$dengjiaName2])) {
// 					$equalStatus2 = true;
// 				    break;
// 				}
// 			}
// 			if (!$equalStatus2) { return false; }
// 		} else
	// 		if (!isEqualTwoArray($zuobiao, $state2[$name])) return false;
	// 	}
	// 	return true;
	// }