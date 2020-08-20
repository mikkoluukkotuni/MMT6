<?php
namespace App\Controller;

use App\Controller\AppController;
use Highcharts\Controller\Component\HighchartsComponent;
use Cake\ORM\TableRegistry;
use Cake\I18n\Time;

class ChartsController extends AppController
{
    public $name = 'Charts';
    public $helpers = ['Highcharts.Highcharts'];
    public $uses = array();

    
    public function initialize() 
    {
        parent::initialize();
        $this->loadComponent('Highcharts.Highcharts');
    }
    
    public function index() 
    {
        $admin = $this->request->session()->read('is_admin');
        $supervisor = ( $this->request->session()->read('selected_project_role') == 'supervisor' ) ? 1 : 0;
        
        // When the chart limits are updated this is where they are saved
        if ($this->request->is('post')) {
            $data = $this->request->data;
            
            // If user tries to select more than 52 weeks, display error and don't save time limits
            if (($data['yearmin'] < $data['yearmax']) && (53 - $data['weekmin'] + $data['weekmax'] > 52)) {
                $this->Flash->error(__('Can\'t display more than 52 weeks'));
            } else {
                $chartLimits['weekmin'] = $data['weekmin'];
                $chartLimits['weekmax'] = $data['weekmax'];
                $chartLimits['yearmin'] = $data['yearmin'];
                $chartLimits['yearmax'] = $data['yearmax'];
                $this->request->session()->write('chartLimits', $chartLimits);
                
                // refreshin the page to apply the new limits
                $page = $_SERVER['PHP_SELF'];
            }

        }
        
        // Set the stock limits for the chart limits
        // They are only set once, if the "chartLimits" cookie is not in the session
        if (!$this->request->session()->check('chartLimits')) {
            $time = Time::now();
            
            // show last year, current year and next year
            $chartLimits['weekmin'] = 1;
            $chartLimits['weekmax'] = date('W', strtotime($time));
            $chartLimits['yearmin'] = $time->year;
            $chartLimits['yearmax'] = $time->year;
            
            $this->request->session()->write('chartLimits', $chartLimits);
        }
        
        // Loadin the limits to a variable
        $chartLimits = $this->request->session()->read('chartLimits');
        
        // The ID of the currently selected project
        $project_id = $this->request->session()->read('selected_project')['id'];
        
        
        // Get all the data for the charts, based on the chartlimits
        // Fuctions in "ChartsTable.php"
        $weeklyreports = $this->Charts->reports($project_id, $chartLimits['weekmin'], $chartLimits['weekmax'], 
            $chartLimits['yearmin'], $chartLimits['yearmax']
        );
        $allTheWeeks = $this->Charts->weekList($chartLimits['weekmin'], $chartLimits['weekmax'], 
            $chartLimits['yearmin'], $chartLimits['yearmax']
        );

        // Line chart displaying the cumulative amount of hours done in the project
        $totalhourData = $this->Charts->totalhourLineData($project_id, $allTheWeeks, $chartLimits['weekmin'], 
            $chartLimits['weekmax'], $chartLimits['yearmin'], $chartLimits['yearmax']
        );

        $projectStartDate = clone $this->request->session()->read('selected_project')['created_on'];
        $projectEndDate = $this->request->session()->read('selected_project')['finished_date'];

        $dateOfChartUpdates = new Time('2020-08-01');
        
        // For some charts data is only created (and chart displayed) if project has reports and hours
        if (($admin || $supervisor) && sizeof($weeklyreports['id']) > 0 &&  $this->Charts->getTotalHours($project_id) > 0 && $projectStartDate > $dateOfChartUpdates) {
            $this->request->session()->write('displayCharts', true);

            $earnedValueData = $this->Charts->earnedValueData($project_id, $projectStartDate, $projectEndDate);
            $earnedValueChart = $this->earnedValueChart($earnedValueData);
            
            // earnedValueChart
            $earnedValueChart->xAxis->categories = $earnedValueData[0]['weekList'];
            // $earnedValueChart->xAxis->plotLines = array('color' => '#FF0000', 'width' => 2, 'value' => 10);
            foreach ($earnedValueData as $data) {
                $earnedValueChart->series[] = array(
                    'name' => $data['name'],
                    'data' => $data['values'],
                    'marker' => $data['marker'],
                    'type' => $data['type'],
                    'dashStyle' => $data['dashStyle'],
                    'lineWidth' => $data['lineWidth'],
                    'color' => $data['color']
                );
            }    
            
            $earnedValueData2 = $this->Charts->earnedValueData2($project_id, $projectStartDate, $projectEndDate);
            $earnedValueChart2 = $this->earnedValueChart2($earnedValueData2);
            
            // earnedValueChart
            $earnedValueChart2->xAxis->categories = $earnedValueData2[0]['weekList'];
            // $earnedValueChart->xAxis->plotLines = array('color' => '#FF0000', 'width' => 2, 'value' => 10);
            foreach ($earnedValueData2 as $data) {
                $earnedValueChart2->series[] = array(
                    'name' => $data['name'],
                    'data' => $data['values'],
                    'marker' => $data['marker'],
                    'type' => $data['type'],
                    'dashStyle' => $data['dashStyle'],
                    'lineWidth' => $data['lineWidth'],
                    'color' => $data['color']
                );
            }  
        } else {
            $this->request->session()->write('displayCharts', false);
        }
        

        $phaseData = $this->Charts->phaseAreaData($weeklyreports['id']);
        $reqData = $this->Charts->reqColumnData($weeklyreports['id']);
        $commitData = $this->Charts->commitAreaData($weeklyreports['id']);
        $testcaseData = $this->Charts->testcaseAreaData($weeklyreports['id']);

        // $earnedValueData = $this->Charts->earnedValueData($project_id, $projectStartDate, $projectEndDate);
        
        // Bar chart displaying the amount of hours in each category
        $hoursData = $this->Charts->hoursData($project_id);
        
        // Line chart displaying the amount of hours done by the team per week 
        $hoursperweekData = $this->Charts->hoursPerWeekData($project_id, $allTheWeeks, $chartLimits['weekmin'], 
            $chartLimits['weekmax'], $chartLimits['yearmin'], $chartLimits['yearmax']
        );
        

        $riskData = $this->Charts->riskData($weeklyreports['id'], $project_id);
        $hoursComparisonData = $this->Charts->hoursComparisonData($allTheWeeks, $chartLimits['weekmin'], 
            $chartLimits['weekmax'], $chartLimits['yearmin'], $chartLimits['yearmax']
        );


        // Get the chart objects for the charts
        // these objects come from functions in this controller
        $phaseChart = $this->phaseChart();
        $reqChart = $this->reqChart();
        $reqPercentChart = $this->reqPercentChart();
        $commitChart = $this->commitChart();
        $testcaseChart = $this->testcaseChart();        
        $totalhourChart = $this->totalhourChart();
        $hoursPerWeekChart = $this->hoursPerWeekChart();  
        $hoursChart = $this->hoursChart();
        $hoursChart2 = $this->hoursChart2();     
        $risksProbChart = $this->risksProbChart();
        $risksImpactChart = $this->risksImpactChart();
        $risksCombinedChart = $this->risksCombinedChart();
        $derivedChart = $this->derivedChart();
        $hoursComparisonChart = $this->hoursComparisonChart();
        // $earnedValueChart = $this->earnedValueChart($earnedValueData);

        
        // Insert the data in to the charts, one by one
        // phaseChart
        $phaseChart->xAxis->categories = $weeklyreports['weeks'];
        $phaseChart->series[] = array(
            'name' => 'Total phases planned',
            'data' => $phaseData['phaseTotal']
        );
        $phaseChart->series[] = array(
            'name' => 'Phase',
            'data' => $phaseData['phase']
        );
        
        // reqChart
        $reqChart->xAxis->categories = $weeklyreports['weeks'];
        $reqChart->series[] = array(
            'name' => 'Product Backlog',
            'data' => $reqData['new']
        );
        $reqChart->series[] = array(
            'name' => 'Sprint Backlog',
            'data' => $reqData['inprogress']
        );
        $reqChart->series[] = array(
            'name' => 'Done',
            'data' => $reqData['closed']
        );
        $reqChart->series[] = array(
            'name' => 'Rejected',
            'data' => $reqData['rejected']
        );
                
        // reqPercentChart
        $reqPercentChart->xAxis->categories = $weeklyreports['weeks'];
        $reqPercentChart->series[] = array(
            'name' => 'Product Backlog',
            'data' => $reqData['new']
        );
        $reqPercentChart->series[] = array(
            'name' => 'Sprint Backlog',
            'data' => $reqData['inprogress']
        );
        $reqPercentChart->series[] = array(
            'name' => 'Done',
            'data' => $reqData['closed']
        );
        $reqPercentChart->series[] = array(
            'name' => 'Rejected',
            'data' => $reqData['rejected']
        );
        
        // commitChart
        $commitChart->xAxis->categories = $weeklyreports['weeks'];    
        $commitChart->series[] = array(
            'name' => 'commits',
            'data' => $commitData['commits']
        );
        
        // testcaseChart
        $testcaseChart->xAxis->categories = $weeklyreports['weeks'];
        $testcaseChart->series[] = array(
            'name' => 'Total test cases',
            'data' => $testcaseData['testsTotal']
        );
        $testcaseChart->series[] = array(
            'name' => 'Passed test cases',
            'data' => $testcaseData['testsPassed']
        );
        
        // hoursChart
        $hoursChart->series[] = array(
            'name' => 'Hour types',
            'data' => array(
                $hoursData[1],
                $hoursData[2],
                $hoursData[3],
                $hoursData[4],
                $hoursData[5],
                $hoursData[6],
                $hoursData[7],
                $hoursData[8],
                $hoursData[9]
            )
        );

        // hoursChart2  
        if (array_sum($hoursData) > 0) {
            $hoursChart2->series[] = array(
                'name' => 'Hour types',
                'data' => array(
                    round($hoursData[1]/array_sum($hoursData)*100, 0, PHP_ROUND_HALF_UP),
                    round($hoursData[2]/array_sum($hoursData)*100, 0, PHP_ROUND_HALF_UP),
                    round($hoursData[3]/array_sum($hoursData)*100, 0, PHP_ROUND_HALF_UP),
                    round($hoursData[4]/array_sum($hoursData)*100, 0, PHP_ROUND_HALF_UP),
                    round($hoursData[5]/array_sum($hoursData)*100, 0, PHP_ROUND_HALF_UP),
                    round($hoursData[6]/array_sum($hoursData)*100, 0, PHP_ROUND_HALF_UP),
                    round($hoursData[7]/array_sum($hoursData)*100, 0, PHP_ROUND_HALF_UP),
                    round($hoursData[8]/array_sum($hoursData)*100, 0, PHP_ROUND_HALF_UP),
                    round($hoursData[9]/array_sum($hoursData)*100, 0, PHP_ROUND_HALF_UP)
                )
            );
        } else {
            $hoursChart2->series[] = array(
                'name' => 'Hour types',
                'data' => array_fill(1, 9, 0)
            );
        }
              

        
        // totalhourChart
        $totalhourChart->xAxis->categories = $allTheWeeks;    
        $totalhourChart->series[] = array(
            'name' => 'total hours',
            'data' => $totalhourData
        );
        
        //workinghours per week  
        $hoursPerWeekChart->xAxis->categories = $allTheWeeks;
        $hoursPerWeekChart->series[] = array(
            'name' => 'Working hours per week',
            'data' => $hoursperweekData
        );
        
        // risksProbChart
        $risksProbChart->xAxis->categories = $weeklyreports['weeks'];        
        foreach ($riskData as $risk) {            
            $risksProbChart->series[] = array(
                'name' => $risk['name'],
                'data' => $risk['probability']
            );        
        }
        
        
        // risksImpactChart
        $risksImpactChart->xAxis->categories = $weeklyreports['weeks'];        
        foreach ($riskData as $risk) {            
            $risksImpactChart->series[] = array(
                'name' => $risk['name'],
                'data' => $risk['impact']
            );        
        }
        
        
        // risksCombinedChart
        $risksCombinedChart->xAxis->categories = $weeklyreports['weeks'];        
        foreach ($riskData as $risk) {            
            $risksCombinedChart->series[] = array(
                'name' => $risk['name'],
                'data' => $risk['combined']
            );        
        }     
        
              
        // chart for derived metrics
        $derivedChart->xAxis->categories = $weeklyreports['weeks'];
        $derivedChart->series[] = array(
            'name' => 'Total test cases',
            'data' => $testcaseData['testsTotal']
        );
        $derivedChart->series[] = array(
            'name' => 'Passed test cases',
            'data' => $testcaseData['testsPassed']
        );


        // Total hours of each public project
        $hoursComparisonChart->xAxis->categories = $allTheWeeks;    
        foreach ($hoursComparisonData as $projectData) {
            $hoursComparisonChart->series[] = array(
                'name' => $projectData['name'],
                'data' => $projectData['data']
            );
        }

        // This sets the charts visible in the actual charts page "Charts/index.php"
        $this->set(compact('phaseChart', 'reqChart', 'commitChart', 'testcaseChart', 'hoursChart', 'hoursChart2', 'totalhourChart', 
            'hoursPerWeekChart', 'reqPercentChart', 'risksProbChart', 'risksImpactChart', 'risksCombinedChart', 
            'derivedChart', 'hoursComparisonChart', 'earnedValueChart', 'earnedValueChart2')
        );
    }
    
    // All the following functions are similar
    // They create a custom chart object and return it
    // Unfortunately the functions have to be in the controller, 
    // because the chart objects cannot be created outside of the controller
    
    /* 12.3.2016: Total renovation of the charts:
     * - slight change to looks, alignments fixed
     *   -> now all charts have the same appearance and size
     * - labels on axes fixed
     * - commit chart changed from area to column diagram
     * - working hour chart makes more sense now
     *   -> and the numbers on columns are no longer blurred
     * - requirements charts under one header (with subheaders)
     * Requirement ID: 7 (Andy)
     */

    public function earnedValueChart($earnedValueData) 
    {
    	$myChart = $this->Highcharts->createChart();
    	$myChart->chart->renderTo = 'valuewrapper';
    	$myChart->chart->type = 'line';
    
    	$myChart->title = array(
        	'text' => 'Earned value chart - estimated parts stay in budget',
        	'y' => 20,
        	'align' => 'center',
        	'styleFont' => '18px Metrophobic, Arial, sans-serif',
        	'styleColor' => '#0099ff',
        );
    	// $myChart->subtitle->text = "per week";

    	$myChart->chart->backgroundColor->linearGradient = array(0, 0, 0, 300);
        $myChart->chart->backgroundColor->stops = array(array(0, 'rgb(217, 217, 255)'), array(1, 'rgb(255, 255, 255)'));
        $myChart->legend->itemStyle = array('color' => '#222');
        $myChart->legend->backgroundColor->linearGradient = array(0, 0, 0, 25);
        $myChart->legend->backgroundColor->stops = array(array(0, 'rgb(217, 217, 217)'), array(1, 'rgb(255, 255, 255)'));

        $text = "Current week: " . $earnedValueData[6]['currentWeek'] .
            
            ", Estimated 100% hours: week " . $earnedValueData[6]['estimatedWeekFullHours'] . 
            ", Estimated 100% readiness: week " . $earnedValueData[6]['estimatedCompletionWeek'] . 
            ", Planned 100% readiness: week " . $earnedValueData[6]['plannedCompletionWeek'] . 
            "<br/>DR (Degree of Readiness: " . $earnedValueData[6]['DR'] .
            ", AC (Actual Costs): " . $earnedValueData[6]['AC'] . " hours" .
            ", BAC (Budget At Completion): " . $earnedValueData[6]['BAC'] . " hours" .
            "<br/>EAC (Estimated Actual Costs): " . round($earnedValueData[6]['EAC'], 1) . " h" .  
            ", CPI (Cost Performance Index): " . round($earnedValueData[6]['CPI'], 2) .  
            ", SPI (Schedule Performance Index): " . round($earnedValueData[6]['SPI'], 2) .
            ", <b>VAC (Variance At Completion): " . round($earnedValueData[6]['VAC'], 1) . " h" .
            ", Schedule variance at completion: " . round($earnedValueData[6]['SVAC'], 0) . " weeks</b>" . 
            "<br/>Weeks used: " . $earnedValueData[6]['weeksUsed'] . 
            ", Weeks budgeted: " . $earnedValueData[6]['weeksBudgeted'] .
            ", Weeks estimated: " . $earnedValueData[6]['weeksEstimated'] ;
        $myChart->caption->text = $text;
        
        // labels of axes    	
        $myChart->xAxis->title->text = 'Week number';
        $myChart->yAxis->title->text = 'Cost (hours)';


        
        // $myChart->colors = array('#fc0303', '#036ffc', '#068a19', '#fc08f8');
        
        // tooltips etc
        $myChart->tooltip->formatter = $this->Highcharts->createJsExpr("function() {
            return 'Cost: ' +' <b>'+
            Highcharts.numberFormat(this.y, 0) +'</b><br/>Week number '+ this.x +'<br/>Line: ' + this.series.name;}");
        $myChart->plotOptions->area->marker->enabled = false;
    
    	return $myChart;
    }


    public function earnedValueChart2($earnedValueData) 
    {
    	$myChart = $this->Highcharts->createChart();
    	$myChart->chart->renderTo = 'valuewrapper2';
    	$myChart->chart->type = 'line';
    
    	$myChart->title = array(
        	'text' => 'Earned value chart - estimated parts based on actual data',
        	'y' => 20,
        	'align' => 'center',
        	'styleFont' => '18px Metrophobic, Arial, sans-serif',
        	'styleColor' => '#0099ff',
        );
    	// $myChart->subtitle->text = "per week";

    	$myChart->chart->backgroundColor->linearGradient = array(0, 0, 0, 300);
        $myChart->chart->backgroundColor->stops = array(array(0, 'rgb(217, 217, 255)'), array(1, 'rgb(255, 255, 255)'));
        $myChart->legend->itemStyle = array('color' => '#222');
        $myChart->legend->backgroundColor->linearGradient = array(0, 0, 0, 25);
        $myChart->legend->backgroundColor->stops = array(array(0, 'rgb(217, 217, 217)'), array(1, 'rgb(255, 255, 255)'));

        $text = "Current week: " . $earnedValueData[6]['currentWeek'] .
            
            ", Estimated 100% hours: week " . $earnedValueData[6]['estimatedWeekFullHours'] . 
            ", Estimated 100% readiness: week " . $earnedValueData[6]['estimatedCompletionWeek'] . 
            ", Planned 100% readiness: week " . $earnedValueData[6]['plannedCompletionWeek'] . 
            "<br/>DR (Degree of Readiness: " . $earnedValueData[6]['DR'] .
            ", AC (Actual Costs): " . $earnedValueData[6]['AC'] . " hours" .
            ", BAC (Budget At Completion): " . $earnedValueData[6]['BAC'] . " hours" .
            "<br/>EAC (Estimated Actual Costs): " . round($earnedValueData[6]['EAC'], 1) . " h" .  
            ", CPI (Cost Performance Index): " . round($earnedValueData[6]['CPI'], 2) .  
            ", SPI (Schedule Performance Index): " . round($earnedValueData[6]['SPI'], 2) .
            ", <b>VAC (Variance At Completion): " . round($earnedValueData[6]['VAC'], 1) . " h" .
            ", Schedule variance at completion: " . round($earnedValueData[6]['SVAC'], 0) . " weeks</b>" . 
            "<br/>Weeks used: " . $earnedValueData[6]['weeksUsed'] . 
            ", Weeks budgeted: " . $earnedValueData[6]['weeksBudgeted'] .
            ", Weeks estimated: " . $earnedValueData[6]['weeksEstimated'] ;
        $myChart->caption->text = $text;
        
        // labels of axes    	
        $myChart->xAxis->title->text = 'Week number';
        $myChart->yAxis->title->text = 'Cost (hours)';


        
        // $myChart->colors = array('#fc0303', '#036ffc', '#068a19', '#fc08f8');
        
        // tooltips etc
        $myChart->tooltip->formatter = $this->Highcharts->createJsExpr("function() {
            return 'Cost: ' +' <b>'+
            Highcharts.numberFormat(this.y, 0) +'</b><br/>Week number '+ this.x +'<br/>Line: ' + this.series.name;}");
        $myChart->plotOptions->area->marker->enabled = false;
    
    	return $myChart;
    }


    
    public function phaseChart() 
    {
        $myChart = $this->Highcharts->createChart();
        $myChart->chart->renderTo = 'phasewrapper';
        $myChart->chart->type = 'area';
        $myChart->title = array(
            'text' => 'Phases',
            'y' => 20,
            'align' => 'center',
            'styleFont' => '18px Metrophobic, Arial, sans-serif',
            'styleColor' => '#0099ff',
        );
        
        /* (This part is removed from other functions. Kept here as backup.)
         $myChart->chart->marginTop = 60;
         $myChart->chart->marginLeft = 90;
         $myChart->chart->marginRight = 30;
         $myChart->chart->marginBottom = 110;
         $myChart->chart->spacingRight = 10;
         $myChart->chart->spacingBottom = 15;
         $myChart->chart->spacingLeft = 0;
         */
        
         // $myChart->chart->alignTicks = FALSE;
        $myChart->chart->backgroundColor->linearGradient = array(0, 0, 0, 300);
        $myChart->chart->backgroundColor->stops = array(array(0, 'rgb(217, 217, 255)'), array(1, 'rgb(255, 255, 255)'));
        
        // legend below the charts
        $myChart->legend->itemStyle = array('color' => '#222');
        $myChart->legend->backgroundColor->linearGradient = array(0, 0, 0, 25);
        $myChart->legend->backgroundColor->stops = array(array(0, 'rgb(217, 217, 217)'), array(1, 'rgb(255, 255, 255)'));
        
        // labels to describe the content of axes
        $myChart->xAxis->title->text = 'Week number';
        $myChart->yAxis->title->text = 'Total number of phases';
        
        // tooltips for the plotted graphs
        $myChart->tooltip->formatter = $this->Highcharts->createJsExpr(
            "function() {return this.series.name +' <b>'+ Highcharts.numberFormat(this.y, 0) 
            +'</b><br/>Week number '+ this.x;}"
        );
        $myChart->plotOptions->area->marker->enabled = false;;
        
        return $myChart;
    }



    
    
    public function reqChart() 
    {
        $myChart = $this->Highcharts->createChart();
        $myChart->chart->renderTo = 'reqwrapper';
        $myChart->chart->type = 'column';
        
        $myChart->title = array(
        		'text' => 'Requirements',
        		'y' => 20,
        		'align' => 'center',
        		'styleFont' => '18px Metrophobic, Arial, sans-serif',
        		'styleColor' => '#0099ff',
        );
        $myChart->subtitle->text = 'in numbers';

        // $myChart->chart->alignTicks = FALSE;
        $myChart->chart->backgroundColor->linearGradient = array(0, 0, 0, 300);
        $myChart->chart->backgroundColor->stops = array(array(0, 'rgb(217, 217, 255)'), array(1, 'rgb(255, 255, 255)'));
        
        // legend below the chart
        $myChart->legend->enabled = true;
        $myChart->legend->layout = 'horizontal';
        $myChart->legend->align = 'center';
        $myChart->legend->verticalAlign  = 'bottom';
        $myChart->legend->itemStyle = array('color' => '#222');
        $myChart->legend->backgroundColor->linearGradient = array(0, 0, 0, 25);
        $myChart->legend->backgroundColor->stops = array(array(0, 'rgb(217, 217, 217)'), array(1, 'rgb(255, 255, 255)'));
        
        // labels to describe the content of axes
        $myChart->xAxis->title->text = 'Week number';
        $myChart->yAxis->title->text = 'Total number of requirements';
        
        // tooltips etc
        $myChart->tooltip->formatter = $this->Highcharts->createJsExpr(
            "function() {return this.y;}"
        );
        $myChart->plotOptions->column->pointPadding = 0.2;
        $myChart->plotOptions->column->borderWidth = 0;
        return $myChart;
    }
    
    public function reqPercentChart() 
    {
    	$myChart = $this->Highcharts->createChart();
    	$myChart->chart->renderTo = "reqpercentwrapper";
    	$myChart->chart->type = "column";
    	$myChart->plotOptions->column->stacking = "percent";
    	
    	$myChart->title = array(
        		'text' => 'Requirements',
        		'y' => 20,
        		'align' => 'center',
        		'styleFont' => '18px Metrophobic, Arial, sans-serif',
        		'styleColor' => '#0099ff',
        );
    	$myChart->subtitle->text = 'in %';

    	// $myChart->chart->alignTicks = FALSE;
    	$myChart->chart->backgroundColor->linearGradient = array(0, 0, 0, 300);
    	$myChart->chart->backgroundColor->stops = array(array(0, 'rgb(217, 217, 255)'), array(1, 'rgb(255, 255, 255)'));
        
        // legend below the charts
    	$myChart->legend->itemStyle = array('color' => '#222');
    	$myChart->legend->backgroundColor->linearGradient = array(0, 0, 0, 25);
    	$myChart->legend->backgroundColor->stops = array(array(0, 'rgb(217, 217, 217)'), array(1, 'rgb(255, 255, 255)'));
        
        // labels to describe the content of axes
    	$myChart->xAxis->title->text = 'Week number';
    	$myChart->yAxis->title->text = '%';
    	
    	// tooltips
    	$myChart->tooltip->formatter = $this->Highcharts->createJsExpr(
            "function() {return ''+ this.series.name +': '+ this.y +' ('+ Math.round(this.percentage) +'%)';}"
        );
    
    	return $myChart;
    }
    
    public function commitChart()
    {
    	$myChart = $this->Highcharts->createChart();
    	$myChart->chart->renderTo = 'commitwrapper';
    	$myChart->chart->type = 'column';
    
    	$myChart->title = array(
        	'text' => 'Commits',
        	'y' => 20,
        	'align' => 'center',
        	'styleFont' => '18px Metrophobic, Arial, sans-serif',
        	'styleColor' => '#0099ff',
        );
		$myChart->subtitle->text = 'in total';

    	// $myChart->chart->alignTicks = FALSE;
    	$myChart->chart->backgroundColor->linearGradient = array(0, 0, 0, 300);
    	$myChart->chart->backgroundColor->stops = array(array(0, 'rgb(217, 217, 255)'), array(1, 'rgb(255, 255, 255)'));
        
        // this chart doesn't need a legend
    	$myChart->legend->enabled = false;
        
        // tooltips
    	$myChart->tooltip->formatter = $this->Highcharts->createJsExpr(
            "function() {return this.series.name +' produced <b>'
            + Highcharts.numberFormat(this.y, 0) +'</b><br/>Week number '+ this.x;}"
        );
    	
    	// labels to describe the content of axes
    	$myChart->xAxis->title->text = 'Week number';
    	$myChart->yAxis->title->text = 'Total number of commits';
   
    	return $myChart;
    }

    public function testcaseChart() 
    {
    	$myChart = $this->Highcharts->createChart();
    	$myChart->chart->renderTo = 'testcasewrapper';
    	$myChart->chart->type = 'area';
    
    	$myChart->title = array(
        	'text' => 'Test cases',
        	'y' => 20,
        	'align' => 'center',
        	'styleFont' => '18px Metrophobic, Arial, sans-serif',
        	'styleColor' => '#0099ff',
        );

    	// $myChart->chart->alignTicks = FALSE;
    	$myChart->chart->backgroundColor->linearGradient = array(0, 0, 0, 300);
    	$myChart->chart->backgroundColor->stops = array(array(0, 'rgb(217, 217, 255)'), array(1, 'rgb(255, 255, 255)'));
        
        // legend below the charts
    	$myChart->legend->itemStyle = array('color' => '#222');
    	$myChart->legend->backgroundColor->linearGradient = array(0, 0, 0, 25);
    	$myChart->legend->backgroundColor->stops = array(array(0, 'rgb(217, 217, 217)'), array(1, 'rgb(255, 255, 255)'));
    	 
    	// tooltips etc
    	$myChart->tooltip->formatter = $this->Highcharts->createJsExpr(
            "function() {return this.series.name +' <b>'+
            Highcharts.numberFormat(this.y, 0) +'</b><br/>Week number '+ this.x;}"
        );
        $myChart->plotOptions->area->marker->enabled = false;
        
    	// labels to describe the content of axes
    	$myChart->xAxis->title->text = 'Week number';
    	$myChart->yAxis->title->text = 'Total number of test cases';
    
    	return $myChart;
    }
    
    // Working hours categorized by type
    public function hoursChart()
    {
    	$myChart = $this->Highcharts->createChart();
    	$myChart->chart->renderTo = 'hourswrapper';
    	$myChart->chart->type = 'column';
    	$myChart->plotOptions->column->stacking = "normal";
    
    	$myChart->title = array(
        	'text' => 'Working hours categorized by type',
        	'y' => 20,
        	'align' => 'center',
        	'styleFont' => '18px Metrophobic, Arial, sans-serif',
        	'styleColor' => '#0099ff',
        );
    	$myChart->subtitle->text = "project total hours - not affected by time limits";

    	// $myChart->chart->alignTicks = FALSE;
    	$myChart->chart->backgroundColor->linearGradient = array(0, 0, 0, 300);
    	$myChart->chart->backgroundColor->stops = array(array(0, 'rgb(217, 217, 255)'), array(1, 'rgb(255, 255, 255)'));
    	// this chart doesn't need a legend
    	$myChart->legend->enabled = false;
    	// labels of axes; unique x-axis
    	$myChart->xAxis->categories = array(
    			'Documentation',
    			'Requirements',
    			'Design',
    			'Implementation',
                'Testing',
                'Meetings',
                'Studying',
                'Other',
                'Lectures'
    	);
    	$myChart->yAxis->title->text = 'Working hours';
		// tooltips etc
    	$myChart->tooltip->formatter = $this->Highcharts->createJsExpr(
            "function() {return 'Hour type total hours: ' +' <b>' +Highcharts.numberFormat(this.y, 0) 
            +'</b><br/>Work type: '+ this.x;}"
        );
    	$myChart->plotOptions->column->dataLabels->enabled = true;
    	$myChart->plotOptions->column->dataLabels->style->textShadow = false;
    	$myChart->plotOptions->column->dataLabels->style->color = '#444';
    	$myChart->plotOptions->column->dataLabels->style->fontSize = '1.2em';
    
    	return $myChart;
    }

    // Working hours categorized by type
    public function hoursChart2()
    {
    	$myChart = $this->Highcharts->createChart();
    	$myChart->chart->renderTo = 'hourswrapper2';
    	$myChart->chart->type = 'pie';
    
    	$myChart->title = array(
        	'text' => 'Working hours categorized by type',
        	'y' => 20,
        	'align' => 'center',
        	'styleFont' => '18px Metrophobic, Arial, sans-serif',
        	'styleColor' => '#0099ff',
        );
    	$myChart->subtitle->text = "percentage of total";

    	$myChart->chart->backgroundColor->linearGradient = array(0, 0, 0, 300);
    	$myChart->chart->backgroundColor->stops = array(array(0, 'rgb(217, 217, 255)'), array(1, 'rgb(255, 255, 255)'));
    	$myChart->legend->enabled = true;
    	// labels of axes; unique x-axis
    	$myChart->xAxis->categories = array(
    			'Documentation',
    			'Requirements',
    			'Design',
    			'Implementation',
                'Testing',
                'Meetings',
                'Studying',
                'Other',
                'Lectures'
        );                

        $myChart->plotOptions->pie->dataLabels->formatter = $this->Highcharts->createJsExpr(
            "function() {return this.series.chart.axes[0].categories[this.point.index] + ': ' + Highcharts.numberFormat(this.y, 0) + '%';}"
        );

        // tooltips etc
    	$myChart->tooltip->formatter = $this->Highcharts->createJsExpr(
            "function() {return 'Hour type percentage: ' +' <b>' +Highcharts.numberFormat(this.y, 0) 
            +'</b><br/>Work type: '+ this.series.chart.axes[0].categories[this.point.index];}"
        );
    
    	return $myChart;
    }
    
    public function hoursPerWeekChart()
    {
    	$myChart = $this->Highcharts->createChart();
    	$myChart->chart->renderTo = 'hoursperweekwrapper';
    	$myChart->chart->type = 'line';

    
    	$myChart->title = array(
        	'text' => 'Working hours',
        	'y' => 20,
        	'align' => 'center',
        	'styleFont' => '18px Metrophobic, Arial, sans-serif',
        	'styleColor' => '#0099ff',
        );
    	$myChart->subtitle->text = "per week";

    	// $myChart->chart->alignTicks = FALSE;
    	$myChart->chart->backgroundColor->linearGradient = array(0, 0, 0, 300);
    	$myChart->chart->backgroundColor->stops = array(array(0, 'rgb(217, 217, 255)'), array(1, 'rgb(255, 255, 255)'));
    	// this chart doesn't need a legend
    	$myChart->legend->enabled = false;
    	
        // labels of axis    	
        $myChart->xAxis->title->text = 'Week number';
	    $myChart->yAxis->title->text = 'Working hours';
    	
	    // tooltips etc
        $myChart->tooltip->formatter = $this->Highcharts->createJsExpr("
            function() {return 'Total hours: ' +' <b>'+ Highcharts.numberFormat(this.y, 0) 
            +'</b><br/>Week number: '+ this.x;}"
        );
    	$myChart->plotOptions->area->marker->enabled = false;
    
    	return $myChart;
    }
    
    public function totalhourChart()
    {
		$myChart = $this->Highcharts->createChart();
		$myChart->chart->renderTo = 'totalhourwrapper';
		$myChart->chart->type = 'line';
	
		$myChart->title = array(
			'text' => 'Total hours',
			'y' => 20,
			'align' => 'center',
			'styleFont' => '18px Metrophobic, Arial, sans-serif',
			'styleColor' => '#0099ff',
		);
		$myChart->subtitle->text = 'cumulative';

		// $myChart->chart->alignTicks = FALSE;
		$myChart->chart->backgroundColor->linearGradient = array(0, 0, 0, 300);
		$myChart->chart->backgroundColor->stops = array(array(0, 'rgb(217, 217, 255)'), array(1, 'rgb(255, 255, 255)'));
		
		// this chart doesn't need a legend
		$myChart->legend->enabled = false;
		
		// labels to describe the content of axes
		$myChart->xAxis->title->text = 'Week number';
		$myChart->yAxis->title->text = 'Total amount of hours';
		
        $myChart->tooltip->formatter = $this->Highcharts->createJsExpr(
            "function() {return 'Total hours at this point ' +' <b>'
            + Highcharts.numberFormat(this.y, 0) +'</b><br/>Week number '+ this.x;}"
        );
        $myChart->plotOptions->area->marker->enabled = false;
        return $myChart;
    }
    
    
    public function risksProbChart() 
    {
        $myChart = $this->Highcharts->createChart();
        $myChart->chart->renderTo = 'risksprobrapper';
        $myChart->chart->type = 'column';
        $myChart->title = array(
            'text' => 'Risks by Probability',
            'y' => 20,
            'align' => 'center',
            'styleFont' => '18px Metrophobic, Arial, sans-serif',
            'styleColor' => '#0099ff',
        );
      
        $myChart->chart->backgroundColor->linearGradient = array(0, 0, 0, 300);
        $myChart->chart->backgroundColor->stops = array(array(0, 'rgb(217, 217, 255)'), array(1, 'rgb(255, 255, 255)'));
        
        // legend below the charts
        $myChart->legend->itemStyle = array('color' => '#222');
        $myChart->legend->backgroundColor->linearGradient = array(0, 0, 0, 25);
        $myChart->legend->backgroundColor->stops = array(array(0, 'rgb(217, 217, 217)'), array(1, 'rgb(255, 255, 255)'));
        
        // labels to describe the content of axes
        $myChart->xAxis->title->text = 'Week number';
        $myChart->yAxis->title->text = 'Probability';
        
        // tooltips for the plotted graphs
        $myChart->tooltip->formatter = $this->Highcharts->createJsExpr(
            "function() {return this.series.name +' <b>'
            + Highcharts.numberFormat(this.y, 0) +'</b><br/>Week number '+ this.x;}"
        );
        $myChart->plotOptions->area->marker->enabled = false;
        
        return $myChart;
    }
    
    public function risksImpactChart() 
    {
        $myChart = $this->Highcharts->createChart();
        $myChart->chart->renderTo = 'risksimpactwrapper';
        $myChart->chart->type = 'column';
        $myChart->title = array(
            'text' => 'Risks by Impact',
            'y' => 20,
            'align' => 'center',
            'styleFont' => '18px Metrophobic, Arial, sans-serif',
            'styleColor' => '#0099ff',
        );
      
        $myChart->chart->backgroundColor->linearGradient = array(0, 0, 0, 300);
        $myChart->chart->backgroundColor->stops = array(array(0, 'rgb(217, 217, 255)'), array(1, 'rgb(255, 255, 255)'));
        
        // legend below the charts
        $myChart->legend->itemStyle = array('color' => '#222');
        $myChart->legend->backgroundColor->linearGradient = array(0, 0, 0, 25);
        $myChart->legend->backgroundColor->stops = array(array(0, 'rgb(217, 217, 217)'), array(1, 'rgb(255, 255, 255)'));
        
        // labels to describe the content of axes
        $myChart->xAxis->title->text = 'Week number';
        $myChart->yAxis->title->text = 'Impact';
        
        // tooltips for the plotted graphs
        $myChart->tooltip->formatter = $this->Highcharts->createJsExpr(
            "function() {return this.series.name +' <b>'
            + Highcharts.numberFormat(this.y, 0) +'</b><br/>Week number '+ this.x;}"
        );
        $myChart->plotOptions->area->marker->enabled = false;
        
        return $myChart;
    }
    
    public function risksCombinedChart() 
    {
        $myChart = $this->Highcharts->createChart();
        $myChart->chart->renderTo = 'riskscombinedwrapper';
        $myChart->chart->type = 'column';
        $myChart->title = array(
            'text' => 'Risks by Probability And Impact',
            'y' => 20,
            'align' => 'center',
            'styleFont' => '18px Metrophobic, Arial, sans-serif',
            'styleColor' => '#0099ff',
        );
      
        $myChart->chart->backgroundColor->linearGradient = array(0, 0, 0, 300);
        $myChart->chart->backgroundColor->stops = array(array(0, 'rgb(217, 217, 255)'), array(1, 'rgb(255, 255, 255)'));
        // legend below the charts
        $myChart->legend->itemStyle = array('color' => '#222');
        $myChart->legend->backgroundColor->linearGradient = array(0, 0, 0, 25);
        $myChart->legend->backgroundColor->stops = array(array(0, 'rgb(217, 217, 217)'), array(1, 'rgb(255, 255, 255)'));
        // labels to describe the content of axes
        $myChart->xAxis->title->text = 'Week number';
        $myChart->yAxis->title->text = 'Value';
        
        // tooltips for the plotted graphs
        $myChart->tooltip->formatter = $this->Highcharts->createJsExpr(
            "function() {return this.series.name +' <b>'
            + Highcharts.numberFormat(this.y, 0) +'</b><br/>Week number '+ this.x;}"
        );
        $myChart->plotOptions->area->marker->enabled = false;
        
        return $myChart;
    }
    
    

    public function derivedChart()
    {        
   	    $myChart = $this->Highcharts->createChart();
    	$myChart->chart->renderTo = 'derivedwrapper';
    	$myChart->chart->type = 'area';
    
    	$myChart->title = array(
        	'text' => 'Test cases',
        	'y' => 20,
        	'align' => 'center',
        	'styleFont' => '18px Metrophobic, Arial, sans-serif',
        	'styleColor' => '#0099ff',
        );

    	// $myChart->chart->alignTicks = FALSE;
    	$myChart->chart->backgroundColor->linearGradient = array(0, 0, 0, 300);
    	$myChart->chart->backgroundColor->stops = array(array(0, 'rgb(217, 217, 255)'), array(1, 'rgb(255, 255, 255)'));
        
        // legend below the charts
    	$myChart->legend->itemStyle = array('color' => '#222');
    	$myChart->legend->backgroundColor->linearGradient = array(0, 0, 0, 25);
    	$myChart->legend->backgroundColor->stops = array(array(0, 'rgb(217, 217, 217)'), array(1, 'rgb(255, 255, 255)'));
    	 
    	// tooltips etc
    	$myChart->tooltip->formatter = $this->Highcharts->createJsExpr(
            "function() {return this.series.name +' <b>'
            + Highcharts.numberFormat(this.y, 0) +'</b><br/>Week number '+ this.x;}"
        );
    	$myChart->plotOptions->area->marker->enabled = false;
        
        // labels to describe the content of axes
    	$myChart->xAxis->title->text = 'Week number';
    	$myChart->yAxis->title->text = 'Total number of test cases';
    
    	return $myChart;
    }    

    public function hoursComparisonChart()
    {
		$myChart = $this->Highcharts->createChart();
		$myChart->chart->renderTo = 'hourscomparisonwrapper';
		$myChart->chart->type = 'line';
	
		$myChart->title = array(
			'text' => 'Total hours of each public project',
			'y' => 20,
			'align' => 'center',
			'styleFont' => '18px Metrophobic, Arial, sans-serif',
			'styleColor' => '#0099ff',
		);

		// $myChart->chart->alignTicks = FALSE;
		$myChart->chart->backgroundColor->linearGradient = array(0, 0, 0, 300);
		$myChart->chart->backgroundColor->stops = array(array(0, 'rgb(217, 217, 255)'), array(1, 'rgb(255, 255, 255)'));
		
        $myChart->legend->itemStyle = array('color' => '#222');
        $myChart->legend->backgroundColor->linearGradient = array(0, 0, 0, 25);
        $myChart->legend->backgroundColor->stops = array(array(0, 'rgb(217, 217, 217)'), array(1, 'rgb(255, 255, 255)'));
		
		// labels to describe the content of axes
		$myChart->xAxis->title->text = 'Week number';
		$myChart->yAxis->title->text = 'Total amount of hours';
		
        $myChart->tooltip->formatter = $this->Highcharts->createJsExpr(
            "function() {return 'Total hours at this point ' +' <b>'+ Highcharts.numberFormat(this.y, 0) 
            +'</b><br/>Week number '+ this.x +'<br/>Project: ' + this.series.name;}"
        );
        $myChart->plotOptions->area->marker->enabled = false;
        return $myChart;
    }
    

    public function isAuthorized($user)
    {      
        return True;
    }
}
