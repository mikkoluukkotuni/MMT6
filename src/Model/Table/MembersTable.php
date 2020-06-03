<?php
namespace App\Model\Table;

use App\Model\Entity\Member;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;

class MembersTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('members');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('Workinghours', [
            'foreignKey' => 'member_id'
        ]);
        $this->hasMany('Weeklyhours', [
            'foreignKey' => 'member_id'
        ]);
    }

    public function validationDefault(Validator $validator)
    {
        $validator
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('project_role', 'create')
            ->notEmpty('project_role')
            ->add('project_role', 'inList', [
                //'rule' => ['inList', ['developer', 'manager', 'supervisor']],
                'rule' => ['inList', ['developer', 'manager', 'supervisor', 'client']],
                'message' => 'Please enter a valid project role'
                ]);

        $validator
            ->add('target_hours', 'valid', ['rule' => 'numeric'])
            ->requirePresence('target_hours', 'create')
            ->notEmpty('target_hours');

        
        // Removed for jQuery UI datepicker
        $validator
            //->add('starting_date', 'valid', ['rule' => 'date'])
            ->allowEmpty('starting_date');

        $validator
            //->add('ending_date', 'valid', ['rule' => 'date'])
            ->allowEmpty('ending_date');


        return $validator;
    }

    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['project_id'], 'Projects'));
        return $rules;
    }
    
    public function getMembers($project_id){
        // returns an array with project members
        // the info is the members id, project role and email from user
        $memberinfo = array();
        //$now = Time::now();
        $members = TableRegistry::get('Members');   
        $query = $members
            ->find()
            ->select(['id', 'project_role', 'user_id', 'target_hours'])    
            ->where(['project_id' => $project_id, 'project_role !=' => 'supervisor'])
            ->andWhere(['project_id' => $project_id, 'project_role !=' => 'client'])
            //->where(['project_id' => $project_id, 'project_role !=' => 'supervisor', 'ending_date >' => $now])
            //->orWhere(['project_id' => $project_id, 'project_role !=' => 'supervisor', 'ending_date IS' => NULL])
            ->toArray();
        
        $users = TableRegistry::get('Users'); 
        foreach ($query as $temp){         
            $query2 = $users
                ->find()
                ->select(['role', 'first_name', 'last_name'])
                ->where(['id =' => $temp->user_id])
                ->toArray();
            
            $temp_memberinfo['id'] = $temp->id;
            $temp_memberinfo['member_name'] = $query2[0]->first_name." ".$query2[0]->last_name." - ".$temp->project_role; 
            
            $memberinfo[] = $temp_memberinfo; 
        }
        return $memberinfo;
    }


    public function predictiveMemberData($project_id, $member_id, $projectStartDate, $endingDate)
    {
        $time = Time::now();
        $currentWeek = date('W');
        
        // Get list of project's members ids
        $members = TableRegistry::get('Members');
        $targetHours = $members
                    ->find()
                    ->select(['target_hours'])
                    ->where(['id =' => $member_id])
                    ->toArray();

        if ($targetHours[0]['target_hours'] == NULL) {
            $targetHours = 100;
        } else {
            $targetHours = $targetHours[0]['target_hours'];
        }

        // Get all hours of the member and store in array in date order
        $workinghours = TableRegistry::get('Workinghours');
        $queryW = $workinghours
                    ->find()
                    ->select(['date', 'duration'])
                    ->where(['member_id =' => $member_id])
                    ->order('date')
                    ->toArray();

        $data = array();
        if (!empty($queryW)) {        

            $weekOfFirstHour = date('W', strtotime($queryW[0]['date']));
            $hoursPerWeek = array();
            $hourSumPerWeek = array();
            $totalSum = 0;
            // Create array $weekList of weeknumbers for x-axis
            $weekList = array();
            $predictedHours = array();

            // Count the total sum of member's hours
            foreach ($queryW as $result) {
                $totalSum += $result['duration'];
            }     
            
            // If project has no estimated completion date then ending date is +20 weeks from project's start date
            if ($endingDate == NULL) {
                $endingDate = $projectStartDate;
                $endingDate->modify('+20 weeks');
            }

            $xLastWeek = date('W', strtotime($endingDate));            

            // Populate array of week numbers to be used as x axis
            if ($weekOfFirstHour > $xLastWeek) {
                for ($i = $weekOfFirstHour; $i <= 52; $i++) {
                    array_push($weekList, $i);
                }
                for ($i = 1; $i <= $xLastWeek; $i++) {
                    array_push($weekList, $i);
                }
            } else {
                for ($i = $weekOfFirstHour; $i <= $xLastWeek; $i++) {
                    array_push($weekList, $i);
                }
            }

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
                if (($time < $endingDate && $weekNumber <= $currentWeek) || $time >= $endingDate) {
                    array_push($hourSumPerWeek, $sum);
                }                
            }

            // Populate array of cumulative average hour sum for each week
            $average = $totalSum / sizeof($hourSumPerWeek);            
            $tempSum = 0;
            for ($i = 1; $i <= sizeof($weekList); $i++) {
                $tempSum += $average;
                array_push($predictedHours, $tempSum);
            }

            // To get the correct week position for target hour marker, add nulls to array for other weeks except the target
            $targetHoursArray = array();
            for ($i = 1; $i < sizeof($weekList); $i++) {
                array_push($targetHoursArray, NULL);
            }
            array_push($targetHoursArray, $targetHours);

            // Store actual working hour data at index 0
            $data[0]['weekList'] = $weekList;
            $data[0]['hours'] = $hourSumPerWeek;
            $data[0]['name'] = 'Actual hours';
            $data[0]['marker'] = array('radius' => 4);

            // Store predicted working hour data at index 1
            $data[1]['hours'] = $predictedHours;
            $data[1]['name'] = 'Predicted hours';
            $data[1]['marker'] = array('radius' => 4);

            $data[2]['hours'] = $targetHoursArray;
            $data[2]['name'] = 'Target';
            $data[2]['marker'] = array('symbol' => 'circle', 'radius' => 6);
        }

        return $data;
        
    }


    public function predictiveProjectData($project_id, $projectStartDate, $endingDate)
    {
        $time = Time::now();
        $currentWeek = date('W');
        
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

        $targetHours = 0;

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
                        $targetHours += $member->target_hours;
                    } else {
                        $targetHours += 100;
                    }
                }                
            }
        }

        $data = array();

        if(!empty($queryW)) {

            $weekOfFirstHour = date('W', strtotime($queryW[0]['date']));
            $hoursPerWeek = array();
            $hourSumPerWeek = array();
            $totalSum = 0;
            // Create array $weekList of weeknumbers for x-axis
            $weekList = array();
            $predictedHours = array();

            // Count the total sum of member's hours
            foreach($queryW as $result) {
                $totalSum += $result['duration'];
            }     
            
            // If project has no estimated completion date then ending date is +20 weeks from project's start date
            if($endingDate == NULL) {
                $endingDate = $projectStartDate;
                $endingDate->modify('+20 weeks');
            }

            $xLastWeek = date('W', strtotime($endingDate));            

            // Populate array of week numbers to be used as x axis
            if($weekOfFirstHour > $xLastWeek) {
                for($i = $weekOfFirstHour; $i <= 52; $i++) {
                    array_push($weekList, $i);
                }
                for($i = 1; $i <= $xLastWeek; $i++) {
                    array_push($weekList, $i);
                }
            } else {
                for($i = $weekOfFirstHour; $i <= $xLastWeek; $i++) {
                    array_push($weekList, $i);
                }
            }

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
                if (($time < $endingDate && $weekNumber <= $currentWeek) || $time >= $endingDate) {
                    array_push($hourSumPerWeek, $sum);
                }                
            }

            // Populate array of cumulative average hour sum for each week
            $average = $totalSum / sizeof($hourSumPerWeek);            
            $tempSum = 0;
            for ($i = 1; $i <= sizeof($weekList); $i++) {
                $tempSum += $average;
                array_push($predictedHours, $tempSum);
            }
            
            // To get the correct week position for target hour marker, add nulls to array for other weeks except the target
            $targetHoursArray = array();
            for ($i = 1; $i < sizeof($weekList); $i++) {
                array_push($targetHoursArray, NULL);
            }
            array_push($targetHoursArray, $targetHours);

            // Store actual working hour data at index 0
            $data[0]['weekList'] = $weekList;
            $data[0]['hours'] = $hourSumPerWeek;
            $data[0]['name'] = 'Actual hours';
            $data[0]['marker'] = array('radius' => 4);

            // Store predicted working hour data at index 1
            $data[1]['hours'] = $predictedHours;
            $data[1]['name'] = 'Predicted hours';
            $data[1]['marker'] = array('radius' => 4);

            $data[2]['hours'] = $targetHoursArray;
            $data[2]['name'] = 'Target';
            $data[2]['marker'] = array('symbol' => 'circle', 'radius' => 6);
        }

        return $data;        
    }


    public function anonymizeAllMembers($project_id)
    {
        // Because ids of anon users might differ from db to db, we need to find the id of anon 1 first
        $users = TableRegistry::get('Users');
        $queryU = $users
            ->find()
            ->where(['email' => 'anon1@a.com'])
            ->toArray();

        $anon1Id = $queryU[0]['id'];
        $anonIds = array();

        // Populate a list of 20 anon user's ids
        for ($i = 0; $i < 20; $i++) {
            array_push($anonIds, ($anon1Id + $i));
        }

        // Get list of project's members
        $members = TableRegistry::get('Members');
        $queryM = $members
            ->find()
            ->where(['project_id' => $project_id, 'project_role !=' => 'supervisor'])
            ->toArray();

        $modifiedMemberlist = array();
        
        for ($i = 0; $i < sizeof($queryM); $i++) {
            $queryM[$i]['user_id'] = $anonIds[$i];
            array_push($modifiedMemberlist, $queryM[$i]);
        }

        return $modifiedMemberlist;

    }
}
