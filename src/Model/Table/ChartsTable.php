<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\I18n\Time;

class ChartsTable extends Table
{
    
    public function initialize(array $config) 
    {
        parent::initialize($config);
    }
    
    // getting the weeklyreport numbers based on the project and limits
    public function reports($project_id, $weekmin, $weekmax, $yearmin, $yearmax)
    {
        $weeklyreports = TableRegistry::get('Weeklyreports');
        $organize = array(); 
        // when we are looking for reports from multiple years
        if ($yearmin != $yearmax) {
            $query = $weeklyreports
                ->find()
                ->select(['id', 'week', 'year'])
                ->where(['project_id' => $project_id, 
                    'week >=' => $weekmin, 
                    'year' => $yearmin]) // min year, weeknum is min or bigger
                ->orWhere(['project_id' => $project_id, 
                    'week <=' => $weekmax, 
                    'year' => $yearmax]) // max year, but weeknumber is max or smaller
                ->toArray();
        
            $query2 = $weeklyreports
                ->find()
                ->select(['id', 'week', 'year'])
                ->Where(['project_id' => $project_id, 
                    'year >' => $yearmin, 'year <' => $yearmax]) // possible middle year, all weeks are good
                ->toArray();
            // add the middle year reports
            foreach ($query2 as $temp) {
                $temparray = array();
                $temparray['id'] = $temp->id;
                $temparray['week'] = $temp->week;
                $temparray['year'] = $temp->year;

                $organize[] = $temparray ;
            }
        }
        // when looking for reports from a single year
        else {
            // if min and max years are same then we just look at week limits
            $query = $weeklyreports
                ->find()
                ->select(['id', 'week', 'year'])
                ->where(['project_id' => $project_id, 
                    'week >=' => $weekmin, 'week <=' => $weekmax, 
                    'year' => $yearmin])

                ->toArray();
        }
        // add all the reports 
        foreach ($query as $temp) {
            $temparray = array();
            $temparray['id'] = $temp->id;
            $temparray['week'] = $temp->week;
            $temparray['year'] = $temp->year;

            $organize[] = $temparray ;
        }

        // get the weeks and years of the reports
        $week = array();
        $year = array();
        foreach ($organize as $key => $row) { 
            $week[$key] = $row['week'];
            $year[$key] = $row['year'];
        }
        // multisort organizes the array of reports based on the year and week
        array_multisort($year, SORT_ASC, $week, SORT_ASC, $organize);

        $idlist = array();
        $weeklist = array();
        // seperate the id and weeknumber
        foreach ($organize as $temp) {
            $idlist[] = $temp['id'];
            $weeklist[] = $temp['week'];
        }
        // save in the correct format and return
        $data = array();
        $data['id'] = $idlist;
        $data['weeks'] = $weeklist;        
        
        return $data;
    }


    // Return a list of all the weeknumbers in the selected time period
    // This is used for line charts of workinghours
    public function weekList($weekmin, $weekmax, $yearmin, $yearmax)
    {
        $weeklist = array();
        if ($yearmin == $yearmax) {
            for ($i = $weekmin; $i <= $weekmax; $i++) {
                array_push($weeklist, $i);
            }
        } else {
            for ($i = $yearmin; $i <= $yearmax; $i++) {
                if($i == $yearmin) {
                    for( $j = $weekmin; $j <= 52; $j++) {
                        array_push($weeklist, $j);
                    }
                } else if ($i == $yearmax) {
                    for ($j = 1; $j <= $weekmax; $j++) {
                        array_push($weeklist, $j);
                    }
                } else {
                    for ($j = 1; $j <= 52; $j++) {
                        array_push($weeklist, $j);
                    }
                }
            }
        }
        
        return $weeklist;
    }


    public function getTotalHours($project_id)
    {
        $members = TableRegistry::get('Members');

        // get a list of the members in the project
        $query = $members
            ->find()
            ->select(['id'])
            ->where(['project_id =' => $project_id])
            ->toArray();
        $memberlist = array();
        
        if (!empty($query)) {
            foreach ($query as $temp) {
                $memberlist[] = $temp->id;
            }
        }

        $workinghours = TableRegistry::get('Workinghours');
        if (!empty($memberlist)) {
            $queryW = $workinghours
                ->find()
                ->select(['date', 'duration'])
                ->where(['member_id IN' => $memberlist])
                ->order('date')
                ->toArray();
        }

        $totalHours = 0;

        if (!empty($queryW)) {
            foreach ($queryW as $result) {
                $totalHours += $result['duration'];
            }
        }

        return $totalHours;
    }

    // In this data set the predicted values stay in the budget
    public function earnedValueData($project_id, $projectStartDate, $projectEndDate)
    {
        $time = Time::now();
        $currentWeek = date('W');
        $weekList = array();

        // If project has no estimated completion date then ending date is +20 weeks from project's start date
        if ($projectEndDate == NULL) {
            // Have to use clone, otherwise $projectStartDate also changes
            $projectEndDate = clone $projectStartDate;
            $projectEndDate->modify('+20 weeks');
        }      
        
        $xFirstWeek = date('W', strtotime($projectStartDate));     
        $xLastWeek = date('W', strtotime($projectEndDate));           

        // Populate array of week numbers to be used as x axis
        if ($xFirstWeek > $xLastWeek) {
            for ($i = $xFirstWeek; $i <= 52; $i++) {
                array_push($weekList, $i);
            }
            for ($i = 1; $i <= $xLastWeek; $i++) {
                array_push($weekList, $i);
            }
        } else {
            for ($i = $xFirstWeek; $i <= $xLastWeek; $i++) {
                array_push($weekList, $i);
            }
        }

        // Get list of project's members
        $members = TableRegistry::get('Members');
        $query = $members
                    ->find()
                    ->select(['id', 'project_role', 'target_hours'])
                    ->where(['project_id' => $project_id])
                    ->toArray();

        $memberlist = array();

        if(!empty($query)) {
            foreach($query as $temp){
                $memberlist[] = $temp->id;
            }
        }

        // BAC - Budget At Completion (same as target hours in some other charts)
        $BAC = array();
        $targetHoursTotal = 0;
        
        // AC - Actual Costs (same as total hours in some other charts)
        $AC = array();

        // Prediction part of actual costs
        $AC2 = array();
        
        // Get all hours of the member and store in array in date order
        // Also get each members target hours
        $workinghours = TableRegistry::get('Workinghours');
        if(!empty($memberlist)) {
            $queryW = $workinghours
                        ->find()
                        ->select(['date', 'duration'])
                        ->where(['member_id IN' => $memberlist])
                        ->order('date')
                        ->toArray();

            foreach($query as $member) {
                if ($member->project_role == 'developer' || $member->project_role == 'manager') {
                    if ($member->target_hours != NULL) {
                        $targetHoursTotal += $member->target_hours;
                    } else {
                        $targetHoursTotal += 100;
                    }
                }                
            }
            for ($i = 1; $i < sizeof($weekList); $i++) {
                array_push($BAC, NULL);
            }
            array_push($BAC, $targetHoursTotal);

            if(!empty($queryW)) {
                // Populate array of cumulative working hour sum for each week
                $sum = 0;
                foreach ($weekList as $weekNumber) {
                    $hoursLogged = False;
                    foreach ($queryW as $result) {
                        if (date('W', strtotime($result['date'])) == $weekNumber) {
                            $sum += $result['duration'];
                            $hoursLogged = True;
                        }
                    }

                    // If project is not completed only draw data points up to current week
                    if (($time < $projectEndDate && $weekNumber <= $currentWeek) || $time >= $projectEndDate) {
                        array_push($AC, $sum);
                    }                
                }
            }
        }

        $data = array();        
        $readiness = array();

        // PV - Planned Value
        $PV = array();
        // $PV2 = array();
        // EAC - Estimated Actual Costs  -- array is used so the value can be placed at the end of x-axis in the chart
        $EAC = array();

        $metrics = TableRegistry::get('Metrics');
        $reports = TableRegistry::get('Weeklyreports');

        // Get week numbers of all the weeklyreports for this project
        $reportWeeks = $reports
            ->find()
            ->select(['week'])
            ->where(['project_id =' => $project_id])
            // Order is needed in case weeklyreports have not been made in chronological order
            ->order(['year' => 'ASC', 'week' => 'ASC'])
            ->toArray();

        if (!empty($reportWeeks)) {
            $reportWeeks2 = array();
            foreach ($reportWeeks as $temp) {
                array_push($reportWeeks2, $temp['week']);                
            }

            foreach ($weekList as $weekNumber) {
                // If there is a weeklyreport for this week, get readiness value from it and push it to data array
                // to correct index of this week
                
                if (in_array($weekNumber, $reportWeeks2)) {
                    $reportId = $reports
                        ->find()
                        ->select(['id'])
                        ->where(['project_id =' => $project_id, 'week' => $weekNumber])
                        ->toArray();

                    $readinessValue = $metrics
                        ->find()
                        ->select(['value'])
                        ->where(['weeklyreport_id =' => $reportId[0]['id'], 'metrictype_id =' => 10])
                        ->toArray();

                    // If there is a readiness value in this weeklyreport, push it to data array, else
                    // push 0 (only applies to projects that started before implementation of this metric)
                    if (!empty($readinessValue)) {
                        array_push($readiness, $readinessValue[0]['value']);
                    } else {
                        array_push($readiness, 0);
                    }
                // If project is not completed only draw data points up to current week
                // In case no weeklyreport, either push value of previous week or in case of first week a 0
                } else if (($time < $projectEndDate && $weekNumber <= $currentWeek) || $time >= $projectEndDate) {
                    if (sizeof($readiness) > 0) {                        
                        array_push($readiness, $readiness[(sizeof($readiness) - 1)]);
                    } else {
                        array_push($readiness, 0);
                    }
                }
            }
        }

        // SPI - Schedule Performance Index (degree of readiness / (weeks used / weeks budgeted))
        $weeksBudgeted = sizeof($weekList);
        // if ($currentWeek > $)
        $weeksUsed = array_search($currentWeek, $weekList) + 1;        
        $SPI = $readiness[(sizeof($readiness) - 1)]/100 / ($weeksUsed / $weeksBudgeted);

        $avgProgress = 100 / sizeof($weekList);
        $currentProgress = $readiness[(sizeof($readiness) - 1)];
        $weeksLeft = $weeksBudgeted - $weeksUsed;
        $progressLeft = 100 - $currentProgress;
        $weeksEstimated = round(($weeksUsed + ($progressLeft / $avgProgress)), 0, PHP_ROUND_HALF_UP);
        $estimatedCompletionWeek = $weekList[0] + $weeksEstimated - 1;
        $SVAC = $weeksEstimated - $weeksBudgeted;

        // Add values up to this week to PV and NULL values to PV2 so that it's actual values start at correct week
        $average = $BAC[(sizeof($BAC) - 1)] / $weeksBudgeted;            
        $tempSum = 0;
        for ($i = 1; $i <= $weeksBudgeted; $i++) {
            $tempSum += $average;
            array_push($PV, $tempSum);
        }
        
        // BCWP - Budgeted Cost for Work Performed (degree of readiness * actual cost)
        $BCWP = array();
        // BCWP2 for the estimated part
        $BCWP2 = array();
        $DR = $readiness[(sizeof($readiness) - 1)]/100;
        if ($DR == 0) {
            $DR = 1;
        }
        $CPI = $DR / ($AC[(sizeof($AC) - 1)] / $BAC[(sizeof($BAC) - 1)]);

        for ($i = 0; $i < $weeksUsed; $i++) {
            array_push($BCWP, (($readiness[$i]/100) * $BAC[(sizeof($BAC) - 1)]));
            if ($i < $weeksUsed - 1) {
                array_push($BCWP2, NULL);
            }            
        }

        // For the weeks after current week, add predicted values to BCWP2
        $averagePredicted = ($BAC[(sizeof($BAC) - 1)] - $BCWP[(sizeof($BCWP) - 1)]) / ($weeksEstimated - sizeof($AC));       
        
        $tempSum = $BCWP[(sizeof($BCWP) - 1)];
        // First point of this line has to be same as the last point of BCWP
        array_push($BCWP2, $tempSum);
        for ($i = 1; $i <= ($weeksEstimated - sizeof($AC)); $i++) {
            $tempSum += $average;
            array_push($BCWP2, $tempSum);
        }

        for ($i = 1; $i < sizeof($AC); $i++) {
            array_push($AC2, NULL);
        }

        // For the weeks after current week, add budgeted values to AC2
        $averageHoursBudgeted = ($BAC[(sizeof($BAC) - 1)] / $weeksBudgeted);
        $tempSum = $AC[(sizeof($AC) - 1)];
        // First point of this line has to be same as the last point of PV
        array_push($AC2, $tempSum);
        for ($i = 1; $i <= ($weeksEstimated - sizeof($AC)); $i++) {
            $tempSum += $averageHoursBudgeted;
            array_push($AC2, $tempSum);
        }

        // Add required week numbers to weekList if weeksEstimated is more than weeksBudgeted
        // We also have to consider the possibility of estimate completion week being next year
        if ($estimatedCompletionWeek > 52) {            
            if ($weeksEstimated > $weeksBudgeted) {
                $i = $weekList[(sizeof($weekList) - 1)] + 1;
                while (sizeof($weekList) < $weeksEstimated) {
                    if ($i > 52) {
                        $i = 1;
                    }
                    array_push($weekList, $i);
                    $i++;
                }
                for ($i = 1; $i < sizeof($weekList); $i++) {
                    array_push($EAC, NULL);        
                }
                array_push($EAC, ($BAC[(sizeof($BAC) - 1)] / $CPI));
            } else {
                for ($i = 1; $i < $weeksEstimated; $i++) {
                    array_push($EAC, NULL);        
                }
                array_push($EAC, ($BAC[(sizeof($BAC) - 1)] / $CPI));
            }
            $estimatedCompletionWeek = $estimatedCompletionWeek - 52;
        } else {
            if ($weeksEstimated > $weeksBudgeted) {
                while ($weekList[(sizeof($weekList) - 1)] < $estimatedCompletionWeek) {
                    array_push($weekList, ($weekList[(sizeof($weekList) - 1)] + 1));
                }
                for ($i = 1; $i < sizeof($weekList); $i++) {
                    array_push($EAC, NULL);        
                }
                array_push($EAC, $AC2[(sizeof($AC2) - 1)]);
            } else {
                for ($i = 1; $i < $weeksEstimated; $i++) {
                    array_push($EAC, NULL);        
                }
                array_push($EAC, $AC2[(sizeof($AC2) - 1)]);
            }
        }

        $hoursFull = array();
        $weeksToFullHours = ceil(($BAC[(sizeof($BAC) - 1)] - $AC[(sizeof($AC) - 1)]) / $averageHoursBudgeted) + $weeksUsed;

        $i = $weekList[(sizeof($weekList) - 1)] + 1;
        while ($weeksToFullHours > sizeof($weekList)) {
            if ($i > 52) {
                $i = 1;
            }
            array_push($weekList, $i);
            $i++;
        }

        for ($i = 1; $i <= $weeksToFullHours; $i++) {
            if ($i < $weeksToFullHours) {
                array_push($hoursFull, NULL);
            } else {
                array_push($hoursFull, $targetHoursTotal);
            }
        }

        $estimatedWeekFullHours = $weekList[(sizeof($hoursFull) - 1)];

        $data[0]['weekList'] = $weekList;
        // $data[0]['name'] = 'BCWP (Budgeted Cost for Work Performed)';
        $data[0]['name'] = 'BCWP';
        $data[0]['values'] = $BCWP;
        $data[0]['marker'] = array('symbol' => 'triangle', 'radius' => 4);
        $data[0]['type'] = 'line';
        $data[0]['dashStyle'] = 'Solid';
        $data[0]['lineWidth'] = 2;
        $data[0]['color'] = '#ff7b00';
        $data[0]['pointWidth'] = 1;

                // $data[2]['name'] = 'PV (Planned Value) / BCWS';
        $data[1]['name'] = 'BCWP estimate';
        $data[1]['values'] = $BCWP2;
        $data[1]['marker'] = array('symbol' => 'triangle', 'radius' => 1);
        $data[1]['type'] = 'scatter';
        $data[1]['dashStyle'] = 'Dash';
        $data[1]['lineWidth'] = 2;
        $data[1]['color'] = '#ff7b00';
        $data[1]['pointWidth'] = 1;

        // $data[1]['name'] = 'PV (Planned Value) / BCWS';
        $data[2]['name'] = 'BCWS';
        $data[2]['values'] = $PV;
        $data[2]['marker'] = array('symbol' => 'circle', 'radius' => 4);
        $data[2]['type'] = 'line';
        $data[2]['dashStyle'] = 'Solid';
        $data[2]['lineWidth'] = 2;
        $data[2]['color'] = '#0073ed';
        $data[2]['pointWidth'] = 1;

        // $data[3]['name'] = 'AC (Actual Costs) / ACWP';
        $data[3]['name'] = 'ACWP';
        $data[3]['values'] = $AC;
        $data[3]['marker'] = array('symbol' => 'square', 'radius' => 4);
        $data[3]['type'] = 'line';
        $data[3]['dashStyle'] = 'Solid';
        $data[3]['lineWidth'] = 2;
        $data[3]['color'] = '#00c94d';
        $data[3]['pointWidth'] = 1;

        $data[4]['name'] = 'ACWP estimate';
        $data[4]['values'] = $AC2;
        $data[4]['marker'] = array('symbol' => 'square', 'radius' => 1);
        $data[4]['type'] = 'scatter';
        $data[4]['dashStyle'] = 'Dash';
        $data[4]['lineWidth'] = 2;
        $data[4]['color'] = '#00c94d';
        $data[4]['pointWidth'] = 1;
        
        $data[5]['name'] = 'EAC (Estimated Actual Costs)';
        $data[5]['values'] = $EAC;
        $data[5]['marker'] = array('symbol' => 'circle', 'radius' => 6);
        $data[5]['type'] = 'line';
        $data[5]['dashStyle'] = 'Solid';
        $data[5]['lineWidth'] = 2;
        $data[5]['color'] = '#fc08f8';
        $data[5]['pointWidth'] = 1;

        $data[6]['name'] = 'BAC (Budget At Completion)';
        $data[6]['values'] = $BAC;
        $data[6]['marker'] = array('symbol' => 'circle', 'radius' => 6);
        $data[6]['type'] = 'line';
        $data[6]['dashStyle'] = 'Solid';
        $data[6]['lineWidth'] = 2;
        $data[6]['color'] = '#0073ed';
        $data[6]['pointWidth'] = 1;

        $data[6]['AC'] = $AC[(sizeof($AC) - 1)];
        $data[6]['BAC'] = $BAC[(sizeof($BAC) - 1)];
        $data[6]['DR'] = $DR;
        $data[6]['EAC'] = $EAC[(sizeof($EAC) - 1)];
        $data[6]['CPI'] = $CPI;
        $data[6]['SPI'] = $SPI;
        $data[6]['VAC'] = $EAC[(sizeof($EAC) - 1)] - $BAC[(sizeof($BAC) - 1)];
        $data[6]['SVAC'] = $SVAC;
        $data[6]['currentWeek'] = $currentWeek;
        $data[6]['weeksUsed'] = $weeksUsed;
        $data[6]['weeksBudgeted'] = $weeksBudgeted;
        $data[6]['weeksEstimated'] = $weeksEstimated;
        $data[6]['estimatedCompletionWeek'] = $estimatedCompletionWeek;
        $data[6]['estimatedWeekFullHours'] = $estimatedWeekFullHours;
        $data[6]['plannedCompletionWeek'] = $xLastWeek;

        $data[7]['name'] = 'Estimated 100% hours';
        $data[7]['values'] = $hoursFull;
        $data[7]['marker'] = array('symbol' => 'circle', 'radius' => 6);
        $data[7]['type'] = 'line';
        $data[7]['dashStyle'] = 'Solid';
        $data[7]['lineWidth'] = 2;
        $data[7]['color'] = '#303030';
        $data[7]['pointWidth'] = 1;

        // $data[8]['name'] = 'Estimated 100% hours';
        // $data[8]['values'] = array(NULL, NULL, [0, 400], NULL, [0, 400]);
        // $data[8]['marker'] = array('radius' => 1);
        // $data[8]['type'] = 'columnrange';
        // $data[8]['dashStyle'] = 'Solid';
        // $data[8]['lineWidth'] = 1;
        // $data[8]['color'] = '#a6a6a6';
        // $data[8]['pointWidth'] = 1;


        return $data;        
    }


    // In this data set the predicted values are based on actual averages
    public function earnedValueData2($project_id, $projectStartDate, $projectEndDate)
    {
        $time = Time::now();
        $currentWeek = date('W');
        $weekList = array();

        // If project has no estimated completion date then ending date is +20 weeks from project's start date
        if ($projectEndDate == NULL) {
            // Have to use clone, otherwise $projectStartDate also changes
            $projectEndDate = clone $projectStartDate;
            $projectEndDate->modify('+20 weeks');
        }      
        
        $xFirstWeek = date('W', strtotime($projectStartDate));     
        $xLastWeek = date('W', strtotime($projectEndDate));           

        // Populate array of week numbers to be used as x axis
        if ($xFirstWeek > $xLastWeek) {
            for ($i = $xFirstWeek; $i <= 52; $i++) {
                array_push($weekList, $i);
            }
            for ($i = 1; $i <= $xLastWeek; $i++) {
                array_push($weekList, $i);
            }
        } else {
            for ($i = $xFirstWeek; $i <= $xLastWeek; $i++) {
                array_push($weekList, $i);
            }
        }

        // Get list of project's members
        $members = TableRegistry::get('Members');
        $query = $members
                    ->find()
                    ->select(['id', 'project_role', 'target_hours'])
                    ->where(['project_id' => $project_id])
                    ->toArray();

        $memberlist = array();

        if(!empty($query)) {
            foreach($query as $temp){
                $memberlist[] = $temp->id;
            }
        }

        // BAC - Budget At Completion (same as target hours in some other charts)
        $BAC = array();
        $targetHoursTotal = 0;
        
        // AC - Actual Costs (same as total hours in some other charts)
        $AC = array();

        // Prediction part of actual costs
        $AC2 = array();
        
        // Get all hours of the member and store in array in date order
        // Also get each members target hours
        $workinghours = TableRegistry::get('Workinghours');
        if(!empty($memberlist)) {
            $queryW = $workinghours
                        ->find()
                        ->select(['date', 'duration'])
                        ->where(['member_id IN' => $memberlist])
                        ->order('date')
                        ->toArray();

            foreach($query as $member) {
                if ($member->project_role == 'developer' || $member->project_role == 'manager') {
                    if ($member->target_hours != NULL) {
                        $targetHoursTotal += $member->target_hours;
                    } else {
                        $targetHoursTotal += 100;
                    }
                }                
            }
            for ($i = 1; $i < sizeof($weekList); $i++) {
                array_push($BAC, NULL);
            }
            array_push($BAC, $targetHoursTotal);

            if(!empty($queryW)) {
                // Populate array of cumulative working hour sum for each week
                $sum = 0;
                foreach ($weekList as $weekNumber) {
                    $hoursLogged = False;
                    foreach ($queryW as $result) {
                        if (date('W', strtotime($result['date'])) == $weekNumber) {
                            $sum += $result['duration'];
                            $hoursLogged = True;
                        }
                    }

                    // If project is not completed only draw data points up to current week
                    if (($time < $projectEndDate && $weekNumber <= $currentWeek) || $time >= $projectEndDate) {
                        array_push($AC, $sum);
                    }                
                }
            }
        }

        $data = array();        
        $readiness = array();

        // PV - Planned Value
        $PV = array();
        // $PV2 = array();
        // EAC - Estimated Actual Costs  -- array is used so the value can be placed at the end of x-axis in the chart
        $EAC = array();

        $metrics = TableRegistry::get('Metrics');
        $reports = TableRegistry::get('Weeklyreports');

        // Get week numbers of all the weeklyreports for this project
        $reportWeeks = $reports
            ->find()
            ->select(['week'])
            ->where(['project_id =' => $project_id])
            // Order is needed in case weeklyreports have not been made in chronological order
            ->order(['year' => 'ASC', 'week' => 'ASC'])
            ->toArray();

        if (!empty($reportWeeks)) {
            $reportWeeks2 = array();
            foreach ($reportWeeks as $temp) {
                array_push($reportWeeks2, $temp['week']);                
            }

            foreach ($weekList as $weekNumber) {
                // If there is a weeklyreport for this week, get readiness value from it and push it to data array
                // to correct index of this week
                
                if (in_array($weekNumber, $reportWeeks2)) {
                    $reportId = $reports
                        ->find()
                        ->select(['id'])
                        ->where(['project_id =' => $project_id, 'week' => $weekNumber])
                        ->toArray();

                    $readinessValue = $metrics
                        ->find()
                        ->select(['value'])
                        ->where(['weeklyreport_id =' => $reportId[0]['id'], 'metrictype_id =' => 10])
                        ->toArray();

                    // If there is a readiness value in this weeklyreport, push it to data array, else
                    // push 0 (only applies to projects that started before implementation of this metric)
                    if (!empty($readinessValue)) {
                        array_push($readiness, $readinessValue[0]['value']);
                    } else {
                        array_push($readiness, 0);
                    }
                // If project is not completed only draw data points up to current week
                // In case no weeklyreport, either push value of previous week or in case of first week a 0
                } else if (($time < $projectEndDate && $weekNumber <= $currentWeek) || $time >= $projectEndDate) {
                    if (sizeof($readiness) > 0) {                        
                        array_push($readiness, $readiness[(sizeof($readiness) - 1)]);
                    } else {
                        array_push($readiness, 0);
                    }
                }
            }
        }

        // SPI - Schedule Performance Index (degree of readiness / (weeks used / weeks budgeted))
        $weeksBudgeted = sizeof($weekList);
        // if ($currentWeek > $)
        $weeksUsed = array_search($currentWeek, $weekList) + 1;        
        $SPI = $readiness[(sizeof($readiness) - 1)]/100 / ($weeksUsed / $weeksBudgeted);

        $avgProgressBudgeted = 100 / sizeof($weekList);
        $currentProgress = $readiness[(sizeof($readiness) - 1)];
        $weeksLeft = $weeksBudgeted - $weeksUsed;
        $progressLeft = 100 - $currentProgress;
        // $weeksEstimated = round(($weeksUsed + ($progressLeft / $avgProgressBudgeted)), 0, PHP_ROUND_HALF_UP);
        $weeksEstimated = round(($weeksBudgeted / $SPI), 0, PHP_ROUND_HALF_UP);
        $estimatedCompletionWeek = $weekList[0] + $weeksEstimated - 1;
        $SVAC = $weeksEstimated - $weeksBudgeted;

        // Add values up to this week to PV and NULL values to PV2 so that it's actual values start at correct week
        $average = $BAC[(sizeof($BAC) - 1)] / $weeksBudgeted;            
        $tempSum = 0;
        for ($i = 1; $i <= $weeksBudgeted; $i++) {
            $tempSum += $average;
            array_push($PV, $tempSum);
        }
        
        // BCWP - Budgeted Cost for Work Performed (degree of readiness * actual cost)
        $BCWP = array();
        // BCWP2 for the estimated part
        $BCWP2 = array();
        $DR = $readiness[(sizeof($readiness) - 1)]/100;
        if ($DR == 0) {
            $DR = 1;
        }
        $CPI = $DR / ($AC[(sizeof($AC) - 1)] / $BAC[(sizeof($BAC) - 1)]);

        for ($i = 0; $i < $weeksUsed; $i++) {
            array_push($BCWP, (($readiness[$i]/100) * $BAC[(sizeof($BAC) - 1)]));
            if ($i < $weeksUsed - 1) {
                array_push($BCWP2, NULL);
            }            
        }

        // For the weeks after current week, add predicted values to BCWP2
        $averagePredicted = ($BAC[(sizeof($BAC) - 1)] - $BCWP[(sizeof($BCWP) - 1)]) / ($weeksEstimated - sizeof($AC));   
        $tempSum = $BCWP[(sizeof($BCWP) - 1)];
        // First point of this line has to be same as the last point of BCWP
        array_push($BCWP2, $tempSum);
        for ($i = 1; $i <= ($weeksEstimated - sizeof($AC)); $i++) {
            $tempSum += $averagePredicted;
            array_push($BCWP2, $tempSum);
        }

        // Add required week numbers to weekList if weeksEstimated is more than weeksBudgeted
        // We also have to consider the possibility of estimate completion week being next year
        if ($estimatedCompletionWeek > 52) {            
            if ($weeksEstimated > $weeksBudgeted) {
                $i = $weekList[(sizeof($weekList) - 1)] + 1;
                while (sizeof($weekList) < $weeksEstimated) {
                    if ($i > 52) {
                        $i = 1;
                    }
                    array_push($weekList, $i);
                    $i++;
                }
                for ($i = 1; $i < sizeof($weekList); $i++) {
                    array_push($EAC, NULL);        
                }
                array_push($EAC, ($BAC[(sizeof($BAC) - 1)] / $CPI));
            } else {
                for ($i = 1; $i < $weeksEstimated; $i++) {
                    array_push($EAC, NULL);        
                }
                array_push($EAC, ($BAC[(sizeof($BAC) - 1)] / $CPI));
            }
            $estimatedCompletionWeek = $estimatedCompletionWeek - 52;
        } else {
            if ($weeksEstimated > $weeksBudgeted) {
                while ($weekList[(sizeof($weekList) - 1)] < $estimatedCompletionWeek) {
                    array_push($weekList, ($weekList[(sizeof($weekList) - 1)] + 1));
                }
                for ($i = 1; $i < sizeof($weekList); $i++) {
                    array_push($EAC, NULL);        
                }
                array_push($EAC, ($BAC[(sizeof($BAC) - 1)] / $CPI));
            } else {
                for ($i = 1; $i < $weeksEstimated; $i++) {
                    array_push($EAC, NULL);        
                }
                array_push($EAC, ($BAC[(sizeof($BAC) - 1)] / $CPI));
            }
        }

        for ($i = 1; $i < sizeof($AC); $i++) {
            array_push($AC2, NULL);
        }

        // For the weeks after current week, add predicted values to AC2
        $averageHoursPredicted = ($EAC[(sizeof($EAC) - 1)] - $AC[(sizeof($AC) - 1)]) / ($weeksEstimated - sizeof($AC));   
        $tempSum = $AC[(sizeof($AC) - 1)];
        // First point of this line has to be same as the last point of PV
        array_push($AC2, $tempSum);
        for ($i = 1; $i <= ($weeksEstimated - sizeof($AC)); $i++) {
            $tempSum += $averageHoursPredicted;
            array_push($AC2, $tempSum);
        }

        $hoursFull = array();
        $weeksToFullHours = ceil(($BAC[(sizeof($BAC) - 1)] - $AC[(sizeof($AC) - 1)]) / $averageHoursPredicted) + $weeksUsed;

        $i = $weekList[(sizeof($weekList) - 1)] + 1;
        while ($weeksToFullHours > sizeof($weekList)) {
            if ($i > 52) {
                $i = 1;
            }
            array_push($weekList, $i);
            $i++;
        }

        for ($i = 1; $i <= ($weeksToFullHours); $i++) {
            if ($i < $weeksToFullHours) {
                array_push($hoursFull, NULL);
            } else {
                array_push($hoursFull, $targetHoursTotal);
            }
        }    

        $estimatedWeekFullHours = $weekList[(sizeof($hoursFull) - 1)];

        $data[0]['weekList'] = $weekList;
        // $data[0]['name'] = 'BCWP (Budgeted Cost for Work Performed)';
        $data[0]['name'] = 'BCWP';
        $data[0]['values'] = $BCWP;
        $data[0]['marker'] = array('symbol' => 'triangle', 'radius' => 4);
        $data[0]['type'] = 'line';
        $data[0]['dashStyle'] = 'Solid';
        $data[0]['lineWidth'] = 2;
        $data[0]['color'] = '#ff7b00';
        $data[0]['pointWidth'] = 1;

        // $data[2]['name'] = 'PV (Planned Value) / BCWS';
        $data[1]['name'] = 'BCWP estimate';
        $data[1]['values'] = $BCWP2;
        $data[1]['marker'] = array('symbol' => 'triangle', 'radius' => 1);
        $data[1]['type'] = 'scatter';
        $data[1]['dashStyle'] = 'Dash';
        $data[1]['lineWidth'] = 2;
        $data[1]['color'] = '#ff7b00';
        $data[1]['pointWidth'] = 1;

        // $data[1]['name'] = 'PV (Planned Value) / BCWS';
        $data[2]['name'] = 'BCWS';
        $data[2]['values'] = $PV;
        $data[2]['marker'] = array('symbol' => 'circle', 'radius' => 4);
        $data[2]['type'] = 'line';
        $data[2]['dashStyle'] = 'Solid';
        $data[2]['lineWidth'] = 2;
        $data[2]['color'] = '#0073ed';
        $data[2]['pointWidth'] = 1;

        // $data[3]['name'] = 'AC (Actual Costs) / ACWP';
        $data[3]['name'] = 'ACWP';
        $data[3]['values'] = $AC;
        $data[3]['marker'] = array('symbol' => 'square', 'radius' => 4);
        $data[3]['type'] = 'line';
        $data[3]['dashStyle'] = 'Solid';
        $data[3]['lineWidth'] = 2;
        $data[3]['color'] = '#00c94d';
        $data[3]['pointWidth'] = 1;

        $data[4]['name'] = 'ACWP estimate';
        $data[4]['values'] = $AC2;
        $data[4]['marker'] = array('symbol' => 'square', 'radius' => 1);
        $data[4]['type'] = 'scatter';
        $data[4]['dashStyle'] = 'Dash';
        $data[4]['lineWidth'] = 2;
        $data[4]['color'] = '#00c94d';
        $data[4]['pointWidth'] = 1;
        
        $data[5]['name'] = 'EAC (Estimated Actual Costs)';
        $data[5]['values'] = $EAC;
        $data[5]['marker'] = array('symbol' => 'circle', 'radius' => 6);
        $data[5]['type'] = 'line';
        $data[5]['dashStyle'] = 'Solid';
        $data[5]['lineWidth'] = 2;
        $data[5]['color'] = '#fc08f8';
        $data[5]['pointWidth'] = 1;

        $data[6]['name'] = 'BAC (Budget At Completion)';
        $data[6]['values'] = $BAC;
        $data[6]['marker'] = array('symbol' => 'circle', 'radius' => 6);
        $data[6]['type'] = 'line';
        $data[6]['dashStyle'] = 'Solid';
        $data[6]['lineWidth'] = 2;
        $data[6]['color'] = '#0073ed';
        $data[6]['pointWidth'] = 1;

        $data[6]['AC'] = $AC[(sizeof($AC) - 1)];
        $data[6]['BAC'] = $BAC[(sizeof($BAC) - 1)];
        $data[6]['DR'] = $DR;
        $data[6]['EAC'] = $EAC[(sizeof($EAC) - 1)];
        $data[6]['CPI'] = $CPI;
        $data[6]['SPI'] = $SPI;
        $data[6]['VAC'] = $EAC[(sizeof($EAC) - 1)] - $BAC[(sizeof($BAC) - 1)];
        $data[6]['SVAC'] = $SVAC;
        $data[6]['currentWeek'] = $currentWeek;
        $data[6]['weeksUsed'] = $weeksUsed;
        $data[6]['weeksBudgeted'] = $weeksBudgeted;
        $data[6]['weeksEstimated'] = $weeksEstimated;
        $data[6]['estimatedCompletionWeek'] = $estimatedCompletionWeek;
        $data[6]['estimatedWeekFullHours'] = $estimatedWeekFullHours;
        $data[6]['plannedCompletionWeek'] = $xLastWeek;

        $data[7]['name'] = 'Estimated 100% hours';
        $data[7]['values'] = $hoursFull;
        $data[7]['marker'] = array('symbol' => 'circle', 'radius' => 6);
        $data[7]['type'] = 'line';
        $data[7]['dashStyle'] = 'Solid';
        $data[7]['lineWidth'] = 2;
        $data[7]['color'] = '#303030';
        $data[7]['pointWidth'] = 1;

        // $data[8]['name'] = 'Estimated 100% hours';
        // $data[8]['values'] = array(NULL, NULL, [0, 400], NULL, [0, 400]);
        // $data[8]['marker'] = array('radius' => 1);
        // $data[8]['type'] = 'columnrange';
        // $data[8]['dashStyle'] = 'Solid';
        // $data[8]['lineWidth'] = 1;
        // $data[8]['color'] = '#a6a6a6';
        // $data[8]['pointWidth'] = 1;


        return $data;        
    }

    
    // the rest of the functions are for getting the actual data for the charts
    // this is done with multiple querys, based on the project id and the weeklyreport id's
    
    public function testcaseAreaData($idlist)
    {
        $metrics = TableRegistry::get('Metrics');
        
        $testsPassed = array();
        $testsTotal = array();
        
        foreach ($idlist as $temp) {
            
            $query2 = $metrics
                    ->find()
                    ->select(['value'])
                    ->where(['weeklyreport_id =' => $temp, 'metrictype_id =' => 8])
                    ->toArray();
            
            $testsPassed[] = $query2[0]->value;
            
            $query3 = $metrics
                    ->find()
                    ->select(['value'])
                    ->where(['weeklyreport_id =' => $temp, 'metrictype_id =' => 9])
                    ->toArray();
            
            $testsTotal[] = $query3[0]->value;
            
        }
        
        $data = array();
        $data['testsPassed'] = $testsPassed;
        $data['testsTotal'] = $testsTotal;
        
        return $data;
    }
    

    public function commitAreaData($idlist)
    {
        $metrics = TableRegistry::get('Metrics');

        $commits = array();
        
        foreach($idlist as $temp){
            
            $query2 = $metrics
                    ->find()
                    ->select(['value'])
                    ->where(['weeklyreport_id =' => $temp, 'metrictype_id =' => 7])
                    ->toArray();
            
            $commits[] = $query2[0]->value;
            
        }
        
        $data = array();
        $data['commits'] = $commits;
        
        return $data;
    }
    
    
    public function reqColumnData($idlist)
    {
        $metrics = TableRegistry::get('Metrics');

        $new = array();
        $inprogress = array();
        $closed = array();
        $rejected = array();
        
        foreach ($idlist as $temp) {
            
            $query2 = $metrics
                    ->find()
                    ->select(['value'])
                    ->where(['weeklyreport_id =' => $temp, 'metrictype_id =' => 3])
                    ->toArray();
            
            $new[] = $query2[0]->value;
            
            $query3 = $metrics
                    ->find()
                    ->select(['value'])
                    ->where(['weeklyreport_id =' => $temp, 'metrictype_id =' => 4])
                    ->toArray();
            
            $inprogress[] = $query3[0]->value;
            
            $query4 = $metrics
                    ->find()
                    ->select(['value'])
                    ->where(['weeklyreport_id =' => $temp, 'metrictype_id =' => 5])
                    ->toArray();
            
            $closed[] = $query4[0]->value;            
            
            $query5 = $metrics
                    ->find()
                    ->select(['value'])
                    ->where(['weeklyreport_id =' => $temp, 'metrictype_id =' => 6])
                    ->toArray();
            
            $rejected[] = $query5[0]->value;
            
        }

        $data = array();
        $data['new'] = $new;
        $data['inprogress'] = $inprogress;
        $data['closed'] = $closed;
        $data['rejected'] = $rejected;
        
        return $data;
    }
    

    public function phaseAreaData($idlist)
    {
        $metrics = TableRegistry::get('Metrics');
        
        $phase = array();
        $phaseTotal = array();
        
        foreach ($idlist as $temp) {
            
            $query2 = $metrics
                    ->find()
                    ->select(['value'])
                    ->where(['weeklyreport_id =' => $temp, 'metrictype_id =' => 1])
                    ->toArray();
            
            $phase[] = $query2[0]->value;
            
            $query3 = $metrics
                    ->find()
                    ->select(['value'])
                    ->where(['weeklyreport_id =' => $temp, 'metrictype_id =' => 2])
                    ->toArray();
            
            $phaseTotal[] = $query3[0]->value;
            
        }

        $data = array();
        $data['phase'] = $phase;
        $data['phaseTotal'] = $phaseTotal;
        
        return $data;
    }
    

    public function hoursData($project_id)
    {   
        $members = TableRegistry::get('Members');
        
        // get a list of the members in the project
        $query = $members
                ->find()
                ->select(['id'])
                ->where(['project_id =' => $project_id])
                ->toArray();
        $memberlist = array();
        foreach ($query as $temp) {
            $memberlist[] = $temp->id;
        }
        
        $workinghours = TableRegistry::get('Workinghours');
        // get all the different work types one by one
        $data = array();
        if (!empty($memberlist)) {
            for ($i = 1; $i <= 9; $i++) {
                $query = $workinghours
                    ->find()
                    ->select(['duration'])
                    ->where(['worktype_id =' => $i, 'member_id IN' => $memberlist])
                    ->toArray();

                $num = 0;
                // count the total ammount of the duration of the worktype
                foreach ($query as $temp) {
                    $num += $temp->duration;
                }
                $data[$i] = $num;
            }
        } else {
            $num = "";
            for ($i = 1; $i <= 9; $i++) {
                $data[$i] = $num;
            }
        }
        return $data;
    }
    

    public function hoursPerWeekData($project_id, $weeklist, $weekmin, $weekmax, $yearmin, $yearmax)
    {
        $datemin = new Time('midnight');
        $datemin->setISODate($yearmin, $weekmin);
        $datemax = new Time('midnight');
        $datemax->setISODate($yearmax, $weekmax);
        $datemax->modify('+7 days');

        $members = TableRegistry::get('Members');
        
        // get a list of the members in the project
        $query = $members
                ->find()
                ->select(['id'])
                ->where(['project_id =' => $project_id])
                ->toArray();
        $memberlist = array();
        if (!empty($query)) {
            foreach ($query as $temp){
                $memberlist[] = $temp->id;
            }
        }
        $workinghours = TableRegistry::get('Workinghours');
        if (!empty($memberlist)) {
            $queryW = $workinghours
                        ->find()
                        ->select(['date', 'duration'])
                        ->where(['member_id IN' => $memberlist, 'date >= ' => $datemin, 'date <= ' => $datemax])
                        ->toArray();
        }

        $data = array();
        foreach ($weeklist as $temp) {
            
            $sum = 0;
            if (!empty($queryW)) {
                foreach ($queryW as $result) {
                    // date of workinghours need to be turned to week
                    $weekWH = date('W', strtotime($result['date']));
                    if ($temp == $weekWH) {
                        $sum += $result['duration'];
                    }        
                }
            }
            $data[] = $sum;
        }

        return $data;
    }


    public function totalhourLineData($project_id, $weeklist, $weekmin, $weekmax, $yearmin, $yearmax)
    {
        $datemin = new Time('midnight');
        $datemin->setISODate($yearmin, $weekmin);
        $datemax = new Time('midnight');
        $datemax->setISODate($yearmax, $weekmax);
        $datemax->modify('+7 days');

        $members = TableRegistry::get('Members');
        
        // get a list of the members in the project
        $query = $members
                ->find()
                ->select(['id'])
                ->where(['project_id =' => $project_id])
                ->toArray();
        $memberlist = array();
        if (!empty($query)) {
            foreach ($query as $temp) {
                $memberlist[] = $temp->id;
            }
        }

        $workinghours = TableRegistry::get('Workinghours');
        if (!empty($memberlist)) {
            $queryW = $workinghours
                        ->find()
                        ->select(['date', 'duration'])
                        ->where(['member_id IN' => $memberlist])
                        ->order('date')
                        ->toArray();
        }

        $data = array();
        $hoursPerWeek = array();
        $hourSumPerWeek = array();
        $totalSum = 0;

        if (!empty($queryW)) {
            foreach ($queryW as $result) {
                $totalSum += $result['duration'];
            }

            $weekOfFirstHour = date('W', strtotime($queryW[0]['date']));
            $weekOfLastHour = date('W', strtotime($queryW[(sizeof($queryW))-1]['date']));
            $yearOfFirstHour = date('Y', strtotime($queryW[0]['date']));
            $yearOfLastHour = date('Y', strtotime($queryW[(sizeof($queryW))-1]['date']));
            $dateOfFirstHour = $queryW[0]['date'];
            $dateOfLastHour = $queryW[(sizeof($queryW))-1]['date'];

            // get sums per week and store them to array where key is weeknumber         
                for ($i = 1; $i <= 52; $i++) {
                    $sum = 0;
                    foreach ($queryW as $result) {
                        // date of workinghours need to be turned to week
                        $weekWH = date('W', strtotime($result['date']));
                        if ($i == $weekWH) {
                            $sum += $result['duration'];
                        }
                    }
                    $temp = [$i => $sum];
                    $hoursPerWeek = array_replace($hoursPerWeek, $temp);
                }
            

            // count cumulative sum per week and store it in array where key is weeknumber
            $sum = 0;
            
            if ($weekOfFirstHour > $weekOfLastHour) {            
                for ($i = $weekOfFirstHour; $i <= 52; $i++) {
                    $sum += $hoursPerWeek[$i];
                    if ($dateOfLastHour > $datemin) {
                        $temp = [$i => $sum];
                        $hourSumPerWeek = array_replace($hourSumPerWeek, $temp);
                    } else {
                        $temp = [$i => $totalSum];
                        $hourSumPerWeek = array_replace($hourSumPerWeek, $temp);
                    }
                }
                for($i = 1; $i <= ($weekOfFirstHour - 1); $i++) {
                    $sum += $hoursPerWeek[$i];
                    if ($dateOfLastHour > $datemin) {
                        if ($i > $weekmax) {
                            $temp = [$i => 0];
                        } else {
                            $temp = [$i => $sum];
                        }
                        
                        $hourSumPerWeek = array_replace($hourSumPerWeek, $temp);
                    } else {
                        $temp = [$i => $totalSum];
                        $hourSumPerWeek = array_replace($hourSumPerWeek, $temp);
                    }
                }
            } else {
                for ($i = 1; $i <= 52; $i++) {
                    $sum += $hoursPerWeek[$i];
                    if ($dateOfLastHour > $datemin) {
                        if ($i > $weekmax) {
                            $temp = [$i => 0];
                        } else {
                            $temp = [$i => $sum];
                        }
                        $hourSumPerWeek = array_replace($hourSumPerWeek, $temp);
                    } else {
                        $temp = [$i => $totalSum];
                        $hourSumPerWeek = array_replace($hourSumPerWeek, $temp);
                    }
                }
            }            

            if ($dateOfFirstHour <= $datemax) {
                foreach ($weeklist as $week) {
                    $data[] = $hourSumPerWeek[$week];
                }
            }
        }
        return $data;
    }


    // For admin to compare working hours of public projects
    public function hoursComparisonData($weeklist, $weekmin, $weekmax, $yearmin, $yearmax) 
    {
        $projects = TableRegistry::get('Projects');
        $query2 = $projects
            ->find()
            ->select(['id', 'project_name'])
            ->where(['is_public' => 1])
            ->toArray();     
        
        $public_projects = array();
        
        // get id and name of each public project
        foreach ($query2 as $temp) {
            $temp2 = array();
            $temp2['id'] = $temp->id;
            $temp2['project_name'] = $temp->project_name;
            $public_projects[] = $temp2;
        }

        $combined_data = array();

        foreach ($public_projects as $public_project) {
            $data = $this->totalhourLineData($public_project['id'], $weeklist,  $weekmin, $weekmax, $yearmin, $yearmax);

            // store name and a list of weekly workinghours sums for each project
            $tmp = array();
            $tmp['name'] = $public_project['project_name'];
            $tmp['data'] = $data;
            $combined_data[] = $tmp;
        }
        return $combined_data;            
    }   
    

    public function riskData($idlist, $projectId)
    {
        
        $risks = TableRegistry::get('Risks');
        $weeklyRisks = TableRegistry::get('Weeklyrisks');
        
        $data = array();
        
        $projectRisks = $risks->find()->where(['project_id' => $projectId])->toArray();

        
        foreach ($projectRisks as $projectRisk) {        
            $item = array();
            
            $item['name'] = $projectRisk->description;
            
            $probability = array();
            $impact = array();
            $combined = array();
            
            foreach ($idlist as $temp) {            
                $query = $weeklyRisks
                        ->find()
                        ->where(['weeklyreport_id =' => $temp, 'risk_id' => $projectRisk->id])
                        ->toArray();
                
                $probTemp = 0;
                $impactTemp = 0;
                
                if (!empty($query)) {
                    $probTemp = $query[0]->probability;
                    $impactTemp = $query[0]->impact;
                }
                
                $probability[] = $probTemp;
                $impact[] = $impactTemp;
                $combined[] = $probTemp * $impactTemp;
            }
            
            $item['probability'] = $probability;
            $item['impact'] = $impact;
            $item['combined'] = $combined;
            
            $data[] = $item;

        }
        
        return $data;
    }

}
