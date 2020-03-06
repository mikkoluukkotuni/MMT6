<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Highcharts\Controller\Component\HighchartsComponent;
use Cake\I18n\Time;

class MembersController extends AppController
{
    // public $name = 'Charts';
    public $helpers = ['Highcharts.Highcharts'];
    // public $uses = array();

    public function initialize() {
        parent::initialize();
        $this->loadComponent('Highcharts.Highcharts');
    }

    public function index()
    {
        // Only members of the current project are loaded
        $project_id = $this->request->session()->read('selected_project')['id'];   
        $this->paginate = [
            //'contain' => ['Users', 'Projects', 'Workinghours', 'Weeklyhours'],
            'contain' => ['Users', 'Projects', 'Workinghours'],
            'conditions' => array('Members.project_id' => $project_id)
        ];
        $this->set('members', $this->paginate($this->Members));
        $this->set('_serialize', ['members']);

    }

    public function view($id = null)
    {
        // The member with the id "$id" is loaded
        // IF the member is a part of the currently selected project
        $project_id = $this->request->session()->read('selected_project')['id'];
        $member = $this->Members->get($id, [
            'contain' => ['Users', 'Projects', 'Workinghours', 'Weeklyhours'],
            'conditions' => array('Members.project_id' => $project_id)
        ]);
        $this->set('member', $member);
        $this->set('_serialize', ['member']);

        // Chart for workinghour prediction
        // Find and store info that will be needed by chart data function in MembersTable
        $member_id =  $this->request->session()->read('selected_project_memberid', $project_memberid);
        $projectStartDate = clone $this->request->session()->read('selected_project')['created_on'];
        $endingDate = $this->request->session()->read('selected_project')['finished_date'];

        $predictiveMemberChart = $this->predictiveMemberChart();
        $predictiveMemberData = $this->Members->predictiveMemberData($project_id, $member_id, $projectStartDate, $endingDate);
       
        // Define axis data for chart
        $predictiveMemberChart->xAxis->categories = $predictiveMemberData[0]['weekList'];    
        foreach($predictiveMemberData as $data) {
            $predictiveMemberChart->series[] = array(
                'name' => $data['name'],
                'data' => $data['hours']
            );
        }

        // This sets the chart visible in the actual page
        $this->set(compact('predictiveMemberChart'));
    }

    // Predictive working hours chart for individual member
    public function predictiveMemberChart(){
    	$myChart = $this->Highcharts->createChart();
    	$myChart->chart->renderTo = 'predictiveMemberChartWrapper';
    	$myChart->chart->type = 'line';

    
    	$myChart->title = array(
        	'text' => 'Working hours prediction',
        	'y' => 20,
        	'align' => 'center',
        	'styleFont' => '18px Metrophobic, Arial, sans-serif',
        	'styleColor' => '#0099ff',
        );
    	$myChart->subtitle->text = "per week";

    	// $myChart->chart->alignTicks = FALSE;
    	$myChart->chart->backgroundColor->linearGradient = array(0, 0, 0, 300);
    	$myChart->chart->backgroundColor->stops = array(array(0, 'rgb(217, 217, 255)'), array(1, 'rgb(255, 255, 255)'));
        $myChart->legend->itemStyle = array('color' => '#222');
        $myChart->legend->backgroundColor->linearGradient = array(0, 0, 0, 25);
        $myChart->legend->backgroundColor->stops = array(array(0, 'rgb(217, 217, 217)'), array(1, 'rgb(255, 255, 255)'));
    	
        // labels of axes    	
        $myChart->xAxis->title->text = 'Week number';
	    $myChart->yAxis->title->text = 'Working hours';
    	
	    // tooltips etc
    	$myChart->tooltip->formatter = $this->Highcharts->createJsExpr("function() {
        return 'Total hours: ' +' <b>'+
        Highcharts.numberFormat(this.y, 0) +'</b><br/>Week number '+ this.x +'<br/>Project: ' + this.series.name;}");
    	$myChart->plotOptions->area->marker->enabled = false;
    
    	return $myChart;
    }

    
    public function add()
    {
        $project_id = $this->request->session()->read('selected_project')['id'];
        $member = $this->Members->newEntity();
        
        if ($this->request->is('post')) {
            // data from the form is loaded in to the new member object
            $member = $this->Members->patchEntity($member, $this->request->data);
            // the member is made a part of the currently selected project
            $member['project_id'] = $project_id;
            $email = $this->request->data['email'];
            $query = TableRegistry::get('Users')
                ->find()
           	 ->select(['id']) 
            	->where(['email =' => $email])
                ->toArray(); 
            foreach($query as $temp) {
                $id = $temp['id'];
            }
            //Get matching user id's from current project
            $memberQuery = TableRegistry::get('Members')
                ->find()
                ->select('user_id')
                ->where(['user_id =' => $id, 'project_id =' => $project_id])
                ->toArray();
            //If ID doesn't exist in project, proceed
            if(sizeof($memberQuery) == 0) {
                $member['user_id'] = $id;
                
                // Managers are not allowed to add members that are supervisors
                if($member['project_role'] != "supervisor" || $this->request->session()->read('selected_project_role') != 'manager'){
                    
                    
                    if ($this->Members->save($member)) {
                        $this->Flash->success(__('The member has been saved.'));
                        return $this->redirect(['action' => 'index']);
                    } else {
                        $this->Flash->error(__('The member could not be saved. Please, try again.'));
                    }
                }
                else{
                    $this->Flash->error(__('Managers cannot add supervisors'));
                }
            } else {
                $this->Flash->error(__('The member is already part of the project.'));
            }
        }          
        $users = $this->Members->Users->find('list', ['limit' => 200, 'conditions'=>array('Users.role !=' => 'inactive')]);
        $this->set(compact('member', 'users', 'projects'));
        $this->set('_serialize', ['member']);
    }

    public function edit($id = null)
    {
        $project_id = $this->request->session()->read('selected_project')['id'];
        // The selected member is only loaded if the member is a part of the curren project
        $member = $this->Members->get($id, [
            'contain' => [],
            'conditions' => array('Members.project_id' => $project_id)
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            // data is loaded from the form
            $member = $this->Members->patchEntity($member, $this->request->data);
            // it is made sure that the updated member stays in the current project
            $member['project_id'] = $project_id;

            if ($this->Members->save($member)) {
                $this->Flash->success(__('The member has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The member could not be saved. Please, try again.'));
            }
        }
        $users = $this->Members->Users->find('list', ['limit' => 200, 'conditions'=>array('Users.role !=' => 'inactive')]);
        $this->set(compact('member', 'users', 'projects'));
        $this->set('_serialize', ['member']);
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $member = $this->Members->get($id);
        if ($this->Members->delete($member)) {
            $this->Flash->success(__('The member has been deleted.'));
        } else {
            $this->Flash->error(__('The member could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
    
    
    public function isAuthorized($user)
    {   
        // Admin can access every action
        if (isset($user['role']) && $user['role'] === 'admin') {
            return true;
        }
        
        $project_role = $this->request->session()->read('selected_project_role');
        
        // special rules for members controller.

        // managers can add members, but cannot add new supervisors
        if ($this->request->action === 'add') 
        {
            if($project_role == "manager" || $project_role == "supervisor"){
                return True;
            }
        }
        // only supervisors and admins can delete members
        if ($this->request->action === 'delete') 
        {
            if($project_role == "supervisor"){
                return True;
            }

            // This return false is important, because if we didnt have it a manager could also
            // add edit and delete members. This is because after this if block we call the parent
            return False;
        }

        // in addition to supervisors and admins, member can also edit own data
        if ($this->request->action === 'edit') 
        {
            $id_length = ceil(log10(abs($this->request->session()->read('selected_project_memberid') + 1)));
            if($project_role == "supervisor" || $this->request->session()->read('selected_project_memberid') == substr($this->request->url, -$id_length)){
                return True;
            }

            // This return false is important, because if we didnt have it a manager could also
            // add edit and delete members. This is because after this if block we call the parent
            return False;
        }

        // if not trying to add edit delete
        return parent::isAuthorized($user);        
    }
}
