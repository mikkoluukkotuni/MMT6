<?php
namespace App\Model\Table;

use App\Model\Entity\Project;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;

class ProjectsTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('projects');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->hasMany('Members', [
            'foreignKey' => 'project_id'
        ]);
        $this->hasMany('Metrics', [
            'foreignKey' => 'project_id'
        ]);
        $this->hasMany('Weeklyreports', [
            'foreignKey' => 'project_id'
        ]);
    }
    
    public function validationDefault(Validator $validator)
    {
        $validator
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('project_name', 'create')
            ->notEmpty('project_name')
            ->add('project_name', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            // removed this because created_on was changed to use jQuery UI datepicker
            //->add('created_on', 'valid', ['rule' => 'date'])
            ->requirePresence('created_on', 'create')
            ->notEmpty('created_on');

        $validator
            ->add('updated_on', 'valid', ['rule' => 'date'])
            ->allowEmpty('updated_on');

        // because of jQuery UI datepicker used in edit.ctp
        $validator
            //->add('finished_date', 'valid', ['rule' => 'date'])
            ->allowEmpty('finished_date');

        $validator
            ->allowEmpty('description');

        $validator
            ->add('is_public', 'valid', ['rule' => 'boolean'])
            ->requirePresence('is_public', 'create')
            ->notEmpty('is_public');

        return $validator;
    }
    

    // return a list of all the public projects
    public function getPublicProjects()
    {  
        $projects = TableRegistry::get('Projects');
        $query = $projects
            ->find()
            ->select(['id', 'project_name', 'finished_date'])
            ->where(['is_public' => 1])
            ->toArray();
        $publicProjects = array();
        foreach($query as $temp){
            $project = array();
            $project['id'] = $temp->id;
            $project['project_name'] = $temp->project_name;
            $project['finished_date'] = $temp->finished_date;
            $publicProjects[] = $project;
        }
        return $publicProjects;
    }


    // Get ids of all the developer and manager members
    public function getUserMembers($project_id) 
    {
        $members = TableRegistry::get('Members');
        $queryM = $members
                ->find()
                ->select(['id'])
                ->where(['project_id' => $project_id, 'OR' => [['project_role' => 'developer'], ['project_role' => 'manager']], ])
                ->toArray();
                
        $ids = array();
        foreach($queryM as $member){
            $ids[] = $member->id;
        }
        return $ids;
    }
    

    // used for getting the number of project's users for public statistics
    public function getUserMembersCount($project_id) 
    {
        $userMembers = $this->getUserMembers($project_id);        
        return count($userMembers);
    }


    public function getStartDate($project_id)
    {
        $projects = TableRegistry::get('Projects');
        $query = $projects
            ->find()
            ->select(['created_on'])
            ->where(['id' => $project_id])
            ->toArray();

        return $query[0]['created_on'];
    }


    public function getEndDate($project_id)
    {
        $projects = TableRegistry::get('Projects');
        $query = $projects
            ->find()
            ->select(['created_on', 'finished_date'])
            ->where(['id' => $project_id])
            ->toArray();

        if ($query[0]['finished_date'] == NULL) {
            $projectEndDate = clone $query[0]['created_on'];
            $projectEndDate->modify('+20 weeks');
            return $projectEndDate;
        } else {
            return $query[0]['finished_date'];
        }        
    }

    
    public function getEarliestLastSeenDate($project_id)
    {
        $userMembers = $this->getUserMembers($project_id);
        $lastSeenDate = NULL;
        $workinghours = TableRegistry::get('Workinghours');
        $individualLastSeenDates = array();
        if(!empty($memberIds)) {
            foreach($memberIds as $memberId) {
                $queryW = $workinghours
                    ->find()
                    ->select(['date'])
                    ->where(['member_id' => $memberId])
                    ->order(['date' => 'DESC'])
                    ->toArray();
                    var_dump($individualLastSeenDates);
                if(!empty($queryW)) {
                    array_push($individualLastSeenDates, $queryW[0]->date);
                }           
            } 
        }
        if(!empty($individualLastSeenDates)) {
            $lastSeenDate = min($individualLastSeenDates);
        }
        
        return $lastSeenDate;
    }
    

    // Get list of members working hours
    public function getMembersIndividualHours($project_id)
    {
        $memberIds = $this->getUserMembers($project_id);
        
        $workinghours = TableRegistry::get('Workinghours');
        $individualTotalHours = array();
        if(!empty($memberIds)) {
            foreach($memberIds as $memberId) {
                $queryW = $workinghours
                    ->find()
                    ->select(['duration'])
                    ->where(['member_id' => $memberId])
                    ->toArray();

                $sum = 0;
                if(!empty($queryW)) {
                    foreach($queryW as $result) {
                        $sum += $result->duration;
                    }
                }
                array_push($individualTotalHours, $sum);
            }   
        }
        return $individualTotalHours;
    }


    // get the total workinghours of a project
    public function getTotalHours($project_id) 
    {
        $individualTotalHours = $this->getMembersIndividualHours($project_id);
        $sum = 0;

        if(!empty($individualTotalHours)) {
            foreach($individualTotalHours as $membersHours) {
                $sum += $membersHours;
            }
        }
        return $sum;
    }


    public function getMinimumHours($project_id) 
    {
        $individualTotalHours = $this->getMembersIndividualHours($project_id);
        $minimumHours = 0;

        if(!empty($individualTotalHours)) {
            $minimumHours = $individualTotalHours[0];
            foreach($individualTotalHours as $membersHours) {
                if($membersHours < $minimumHours) {
                    $minimumHours = $membersHours;
                }
            }
        }
        return $minimumHours;
    }
    
    
    // new version of the function
    public function getWeeklyhoursDuration($project_id)
    {
        $weeklyreports = TableRegistry::get('Weeklyreports'); 
        $query = $weeklyreports
            ->find()
            ->select(['id'])
            ->where(['project_id' => $project_id])
            ->toArray();
        $ids = array();
        foreach ($query as $temp) {
            $ids[] = $temp->id;
        }
        
        $weeklyhours = TableRegistry::get('Weeklyhours');
        $duration = 0;
        if (!empty($ids)) {
            $query = $weeklyhours
                ->find()
                ->select(['duration'])
                ->where(['weeklyreport_id IN' => $ids])
                ->toArray();            
            if (!empty($query)) {
                foreach($query as $temp){
                    $duration += $temp->duration;
                }
            }
        }    
        return $duration;
    } 


    // get total target hours of a project
    public function getTargetHours($project_id) 
    {
        // Get list of project's members
        $members = TableRegistry::get('Members');
        $query = $members
                    ->find()
                    ->select(['id', 'project_role', 'target_hours'])
                    ->where(['project_id' => $project_id])
                    ->toArray();

        $targetHours = 0;

        // Get all hours of the member and store in array in date order
        // Also get each members target hours
        $workinghours = TableRegistry::get('Workinghours');
        if(!empty($query)) {
            foreach($query as $member) {
                if ($member->project_role == 'developer' || $member->project_role == 'manager') {
                    if ($member->target_hours != NULL) {
                        $targetHours += $member->target_hours;
                    } else {
                        $targetHours += 100;
                    }
                }                
            }
        }
        return $targetHours;
    }


    // This only works with the default metrics (types and order)
    public function getMetrics($project_id)
    {
        $metrics = [];
        $weeklyreports = TableRegistry::get('Weeklyreports'); 
        $query = $weeklyreports
            ->find()
            ->select(['id'])
            ->where(['project_id' => $project_id])
            ->order(['year' => 'DESC', 'week' => 'DESC'])
            ->toArray();

        if (sizeof($query) > 0) {        
            $latestReport = $query[0]->id;

            $metricsTable = TableRegistry::get('Metrics');
            $queryM = $metricsTable
                ->find()
                ->select(['metrictype_id', 'value'])
                ->where(['weeklyreport_id' => $latestReport])
                ->toArray();

            $metrics = $queryM;
        } else {      
            // If new metrics have been added to system then this also has to be updated      
            for ($i = 0; $i < 10; $i++) {
                array_push($metrics, ['metrictype_id' => $i, 'value' => 0]);
            }
        }

        return $metrics;
    } 


    public function getRisks($project_id)
    {
        $risks = [];
        $weeklyreports = TableRegistry::get('Weeklyreports'); 
        $query = $weeklyreports
            ->find()
            ->select(['id'])
            ->where(['project_id' => $project_id])
            // This is to make sure that the latest weeks report is selected 
            ->order(['year' => 'DESC', 'week' => 'DESC'])
            ->toArray();

        if (sizeof($query) > 0) {        
            $latestReport = $query[0]->id;

            $risksTable = TableRegistry::get('Weeklyrisks');
            $queryR = $risksTable
                ->find()
                ->select(['probability', 'impact'])
                ->where(['weeklyreport_id' => $latestReport])
                ->toArray();

            $highRisks = 0;
            foreach ($queryR as $temp) {
                if ($temp['probability'] * $temp['impact'] > 15) {
                    $highRisks++;
                }
            }
            $totalRisks = sizeof($queryR);

            array_push($risks, $highRisks);
            array_push($risks, $totalRisks);
        } else {            
            for ($i = 0; $i < 2; $i++) {
                array_push($risks, 0);
            }
        }

        return $risks;
    } 

    
    // get a list with 'X', 'L' or ' ' for the weeks based on the limits
    // 'X' if that weeks report was returned
    // 'L' if that weeks report should have been returned but its not
    // ' ' if there is no report but its still not late
    public function getWeeklyreportWeeks($project_id, $min, $max, $year)
    {
        $weeklyreports = TableRegistry::get('Weeklyreports'); 
        /* BUG FIX: editing weekly limits now works fine
         */
        $query = $weeklyreports
            ->find()
            ->select(['week', 'created_on'])
            ->where(['project_id' => $project_id, 'week >=' => $min, 'week <=' => $max, 'year' => $year])
            ->toArray();

        $weeks = array();
		$createdates = array();
        foreach ($query as $temp) {
            $weeks[] = $temp->week;
			$createdates[] = $temp->created_on;
        }
        $time = Time::now();
        // with the weeks when the report has not been filled
        $completeList = array();
		
		// iterator for weeks and createdates array, resets to zero after "finishing" with one project's reports
		$i = 0;
        for ($count = $min; $count <= $max; $count++){
            // if the week is found
            if (in_array($count, $weeks)){
				// fetch the weekday when report was sent; protip: Sunday = 0 (so Monday = 1)
				$weekday = date( "w", strtotime($createdates[$i]));
				// also fetch the weeknumber when report was sent
				$weekno = date( "W", strtotime($createdates[$i]));

				// weeklyreport is late
				// IF a) it was created on next week AND the weekday was after monday
				// OR b) it was created several weeks later
                if ( ($weeks[$i] +1 == $weekno && $weekday > 1) || ($weeks[$i] +1 < $weekno) ) {
					$completeList[] = 'L';
                } else {
					$completeList[] = 'X';
				}
				$i++;
            }
            else {
				// if its not late, but there is no report
				$completeList[] = '-';
            }
        }
        return $completeList;
    }


    public function getMinHoursOfMember($project_id)
    {
        return 1;
    }


    public function getLatestDateOfMember($project_id)
    {
        return 1;
    }


    // The logic that determines the status of project goes here
    // Check multiple info of the project to determine if project's predicted status is 1, 2 or 3 (green, yellow, red)
    public function getLatestStatus($project_id, $metrics)
    {
        // This status will be calculated based on substatus values of project (weekly report's overall status, 
        // hour status, risk status...)
        // To adjust importance of a substatus increase or decrease it's values
        $status = 1;

        $currentWeek = date('W');
        $startWeek = date('W', strtotime($this->getStartDate($project_id)));
        $endWeek = date('W', strtotime($this->getEndDate($project_id)));
        $projectLength = $endWeek - $startWeek + 1;
        $weeksUsed = $currentWeek - $startWeek + 1;
        if ($currentWeek <= $endWeek) {
            // Check overall status metric of the latest weekly report
            $latestOverallStatus = 1;
            if (sizeof($metrics) >= 11) {
                if ($metrics[10]['value'] == 3) {
                    $latestOverallStatus = 7;
                } else if ($metrics[10]['value'] == 2) {
                    $latestOverallStatus = 3;
                }    
            }

            // Check total hours
            $hourStatus = 1;
            $targetHours = $this->getTargetHours($project_id);
            $totalHours = $this->getTotalHours($project_id);
            $estimatedHoursPerWeek = $targetHours / $projectLength;
            // Ignore first two weeks of project and check total hours against estimated average
            if ($currentWeek > $startWeek + 2) {
                $targetHoursForThisWeek = ($weeksUsed - 2) * $estimatedHoursPerWeek;
                if ($totalHours < $targetHoursForThisWeek) {
                    $hourStatus = 2;
                }
                if ($totalHours < ($weeksUsed - 3) * $estimatedHoursPerWeek) {
                    $hourStatus = 3;
                }
                if ($totalHours < ($weeksUsed - 4) * $estimatedHoursPerWeek) {
                    $hourStatus = 5;
                }
                if ($totalHours < ($weeksUsed - 4) * $estimatedHoursPerWeek) {
                    $hourStatus = 7;
                }
            }

            // Check degree of readiness metric of the latest weekly report
            $readinessStatus = 1;
            if (sizeof($metrics) >= 11 && ($currentWeek > $startWeek + 2)) {
                if ($metrics[9]['value'] < ($weeksUsed - 2) / $projectLength * 100) {
                    $readinessStatus = 3;
                }
                if ($metrics[9]['value'] < ($weeksUsed - 2) / $projectLength * 100 - 20) {
                    $readinessStatus = 4;
                }
                if ($metrics[9]['value'] < ($weeksUsed - 2) / $projectLength * 100 - 40) {
                    $readinessStatus = 7;
                }
            }


            // Check risks of the latest weekly report
            $riskStatus = 1;
            $highRisks = $this->getRisks($project_id)[0];
            $totalRisks = $this->getRisks($project_id)[1];
            if ($totalRisks > 0) {
                if ($highRisks / $totalRisks >= 0.25) {
                    $riskStatus = 2;
                }
                if ($highRisks / $totalRisks >= 0.5) {
                    $riskStatus = 3;
                }
                if ($highRisks / $totalRisks >= 0.75) {
                    $riskStatus = 7;
                }
            }

            $subStatusSum = $latestOverallStatus + $hourStatus + $readinessStatus + $riskStatus;
            if ($subStatusSum > 5) {
                $status = 2;
            }
            if ($subStatusSum > 9) {
                $status = 3;
            }
        }        

        return $status;
    }
}
