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
        $memberIds = $this->getUserMembers($project_id);
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
            
                if(!empty($queryW)) {
                    array_push($individualLastSeenDates, $queryW[0]->date);
                }           
            } 
        }
        if(!empty($individualLastSeenDates)) {
            $lastSeenDate = date(min($individualLastSeenDates));
        }
        // var_dump(gettype($lastSeenDate));
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
            $minimumHours = min($individualTotalHours);
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


    // [0] is high risks, [1] is total risks
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


    // The logic that determines the substatuses of project goes here
    // Returns array of background color stylings as strings to be used in the statistics tables
    public function getStatusColors($project_id, $metrics)
    {
        $red = 'style="background-color:#ff5757"';
        $yellow = 'style="background-color:#ffef85"';                               
        $green = 'style="background-color:#ccffd7"';
        $statusColors = array();

        // Make each substatus's default color green
        $statusColors['commits'] = $green;
        $statusColors['testCases'] = $green;
        $statusColors['backlog'] = $green;
        $statusColors['done'] = $green;
        $statusColors['risks'] = $green;
        $statusColors['CPI/SPI'] = $green;        
        $statusColors['minimumHours'] = $green;
        $statusColors['lastSeen'] = $green;

        $now = Time::now();
        $startDate = $this->getStartDate($project_id);
        $endDate = $this->getEndDate($project_id);

        if ($now <= $endDate) {
            // The project is divided into four phases
            $interval = date_diff($now, $startDate);
            $intervalWeeks = $interval->format('%a') / 7;

            $metrics = $this->getMetrics($project_id);
            $commits = $metrics[6]['value'];
            $testCasesPassed = $metrics[7]['value'];
            $testCasesTotal = $metrics[8]['value'];
            $prodcutBacklog = $metrics[2]['value'];
            $sprintBacklog = $metrics[3]['value'];
            $done = $metrics[4]['value'];            
            
            // risks[0] = high, risks[1] = total
            $risks = $this->getRisks($project_id);
            $highRisks = $risks[0];
            $totalRisks = $risks[1];
            
            $earnedValueData = $this->getEarnedValueData($project_id);
            $CPI = $earnedValueData[6]['CPI'];
            $SPI = $earnedValueData[6]['SPI'];            

            $minimumHours = $this->getMinimumHours($project_id);
            $lastSeen = Time::parseDate($this->getEarliestLastSeenDate($project_id));

            if ($intervalWeeks <= 5) {
                if ($minimumHours == 0) {
                    $statusColors['minimumHours'] = $yellow;
                }
            } else if ($intervalWeeks <= 10) {
                if ($commits == 0) {
                    $statusColors['commits'] = $red;
                } else if ($commits < $intervalWeeks) {
                    $statusColors['commits'] = $yellow;
                }

                if ($testCasesTotal == 0) {
                    $statusColors['testCases'] = $yellow;
                }

                if ($prodcutBacklog == 0) {
                    $statusColors['backlog'] = $red;
                } else if ($prodcutBacklog < 5 || $sprintBacklog == 0) {
                    $statusColors['backlog'] = $yellow;
                }
                
                if ($totalRisks == 0) {
                    $statusColors['risks'] = $red;
                } else if ($totalRisks < 5) {
                    $statusColors['risks'] = $yellow;
                }

                if ($earnedValueData != NULL && ($CPI < 0.5 || $SPI < 0.5)) {
                    $statusColors['CPI/SPI'] = $yellow;
                }
                
                if ($minimumHours < $intervalWeeks) {
                    $statusColors['minimumHours'] = $red;
                } else if ($minimumHours < $intervalWeeks * 2.5) {
                    $statusColors['minimumHours'] = $yellow;
                }
            } else if ($intervalWeeks <= 15) {
                if ($commits == 0) {
                    $statusColors['commits'] = $red;
                } else if ($commits < $intervalWeeks) {
                    $statusColors['commits'] = $yellow;
                }

                if ($testCasesTotal == 0) {
                    $statusColors['testCases'] = $red;
                } else if ($testCasesTotal < 5) {
                    $statusColors['testCases'] = $yellow;
                }

                if ($prodcutBacklog < 5) {
                    $statusColors['backlog'] = $red;
                } else if ($sprintBacklog == 0) {
                    $statusColors['backlog'] = $yellow;
                }

                if ($done == 0) {
                    $statusColors['done'] = $yellow;
                }
                
                if ($totalRisks == 0 || $highRisks == $totalRisks) {
                    $statusColors['risks'] = $red;
                } else if ($totalRisks < 5 || $highRisks > 2) {
                    $statusColors['risks'] = $yellow;
                }

                if ($earnedValueData != NULL && ($CPI < 0.5 || $SPI < 0.5)) {
                    $statusColors['CPI/SPI'] = $red;
                } else if ($earnedValueData != NULL && ($CPI < 0.9 || $SPI < 0.9)) {
                    $statusColors['CPI/SPI'] = $yellow;
                }
                
                if ($minimumHours < $intervalWeeks * 3) {
                    $statusColors['minimumHours'] = $red;
                } else if ($minimumHours < $intervalWeeks * 5) {
                    $statusColors['minimumHours'] = $yellow;
                }
            } else {
                if ($commits < 5) {
                    $statusColors['commits'] = $red;
                } else if ($commits < $intervalWeeks) {
                    $statusColors['commits'] = $yellow;
                }

                if ($testCasesTotal < 5) {
                    $statusColors['testCases'] = $red;
                } else if ($testCasesPassed == 0) {
                    $statusColors['testCases'] = $yellow;
                }

                if ($prodcutBacklog < 5) {
                    $statusColors['backlog'] = $red;
                } else if ($sprintBacklog == 0) {
                    $statusColors['backlog'] = $yellow;
                }

                if ($done == 0) {
                    $statusColors['done'] = $red;
                } else if ($prodcutBacklog * 0.5 > $done) {
                    $statusColors['done'] = $yellow;
                }

                if ($totalRisks == 0 || $highRisks == $totalRisks) {
                    $statusColors['risks'] = $red;
                } else if ($totalRisks < 5 || $highRisks > 2) {
                    $statusColors['risks'] = $yellow;
                }

                if ($earnedValueData != NULL && ($CPI < 0.5 || $SPI < 0.5)) {
                    $statusColors['CPI/SPI'] = $red;
                } else if ($earnedValueData != NULL && ($CPI < 0.9 || $SPI < 0.9)) {
                    $statusColors['CPI/SPI'] = $yellow;
                }
                
                if ($minimumHours < $intervalWeeks * 3) {
                    $statusColors['minimumHours'] = $red;
                } else if ($minimumHours < $intervalWeeks * 5) {
                    $statusColors['minimumHours'] = $yellow;
                }
            }

            // These conditions are the same regardless of the project's phase
            if ($lastSeen == NULL || date_diff($now, $lastSeen)->format('%a') / 7 > 3) {
                $statusColors['lastSeen'] = $red;
            } else if ($lastSeen == NULL || date_diff($now, $lastSeen)->format('%a') / 7 > 2) {
                $statusColors['lastSeen'] = $yellow;
            }
        }

        return $statusColors;
    }


    public function getWeeklyreportCount($project_id)
    {
        $weeklyreports = TableRegistry::get('Weeklyreports'); 
        $query = $weeklyreports
            ->find()
            ->select(['id'])
            ->where(['project_id' => $project_id])
            ->toArray();
        return sizeof($query);
    }


    public function getEarnedValueData($project_id)
    {
        $projectStartDate = $this->getStartDate($project_id);
        $projectEndDate = $this->getEndDate($project_id);
        $data = NULL;
        
        if ($this->getWeeklyreportCount($project_id) > 0 && $this->getTotalHours($project_id) > 0 && $projectEndDate > Time::now()) {
            $this->Charts = TableRegistry::get('Charts');
            $data = $this->Charts->earnedValueData($project_id, $projectStartDate, $projectEndDate);
        }

        return $data;
    }
}
