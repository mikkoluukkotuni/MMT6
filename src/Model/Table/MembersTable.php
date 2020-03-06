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
            // ->where(['project_id' => $project_id, 'project_role !=' => 'supervisor'])
            // ->andWhere(['project_id' => $project_id, 'project_role !=' => 'client'])
            //->where(['project_id' => $project_id, 'project_role !=' => 'supervisor', 'ending_date >' => $now])
            //->orWhere(['project_id' => $project_id, 'project_role !=' => 'supervisor', 'ending_date IS' => NULL])
            ->toArray();
        
        $users = TableRegistry::get('Users'); 
        foreach($query as $temp){         
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


    public function predictiveMemberData($project_id, $member_id, $projectStartDate, $endingDate){

        // Get all hours of the member and store in array in date order
        $workinghours = TableRegistry::get('Workinghours');
        $queryW = $workinghours
                    ->find()
                    ->select(['date', 'duration'])
                    ->where(['member_id =' => $member_id])
                    ->order('date')
                    ->toArray();

        $data = array();

        $hoursPerWeek = array();
        $hourSumPerWeek = array();
        $totalSum = 0;

        if(!empty($queryW)) {
            // Count the total sum of member's hours
            foreach($queryW as $result) {
                $totalSum += $result['duration'];
            }            

            // Create array $weekList of weeknumbers for x-axis

            // If project has no estimated completion date then ending date is +20 weeks from user's first logged working hour
            if($endingDate == NULL) {
                $endingDate = $projectStartDate;
                $endingDate->modify('+20 weeks');
            }
            $weekOfFirstHour = date('W', strtotime($queryW[0]['date']));
            $xLastWeek = date('W', strtotime($endingDate));
            $weekList = array();

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
            foreach($weekList as $weekNumber) {
                foreach($queryW as $result) {
                    if(date('W', strtotime($result['date'])) == $weekNumber) {
                        $sum += $result['duration'];
                    }
                }
                array_push($hourSumPerWeek, $sum);
            }
            
            // Store actual working hour data at index 0
            $data[0]['weekList'] = $weekList;
            $data[0]['hours'] = $hourSumPerWeek;
            $data[0]['name'] = 'Actual hours';

            // Populate array of cumulative average hour sum for each week
            $average = $totalSum / sizeof($data[0]['hours']);
            $predictedHours = array();
            $tempSum = 0;
            for($i = 1; $i <= sizeof($weekList); $i++) {
                $tempSum += $average;
                array_push($predictedHours, $tempSum);
            }

            // Store predicted working hour data at index 1
            $data[1]['hours'] = $predictedHours;
            $data[1]['name'] = 'Predicted hours';

        }

        return $data;
    }
}
