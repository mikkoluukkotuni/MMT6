<?php echo $this->Highcharts->includeExtraScripts(); ?>
<?php use Cake\I18n\Time; ?>

<div class="statistics">
        <h3><?= __('Edit limits') ?></h3> 
        <?= $this->Form->create() ?>
            <div id="chart-limits">
            <?php
                // Set min and max values for input fields
                $time = Time::now();
                echo $this->Form->input('weekmin', array('type' => 'number', 'min' => 1, 'max' => 52, 'value' => $this->request->session()->read('chart_limits')['weekmin']));
                echo $this->Form->input('weekmax', array('type' => 'number', 'min' => 1, 'max' => 52, 'value' => $this->request->session()->read('chart_limits')['weekmax']));
                echo $this->Form->input('yearmin', array('type' => 'number', 'min' => 2015, 'max' => $time->year, 'value' => $this->request->session()->read('chart_limits')['yearmin']));
                echo $this->Form->input('yearmax', array('type' => 'number', 'min' => 2015, 'max' => $time->year, 'value' => $this->request->session()->read('chart_limits')['yearmax']));
            ?>
            </div>
            <button>Submit</button>
        <?= $this->Form->end() ?>
</div>

<div class="metrics index large-9 medium-8 columns content float: left">

    <?php 
    // Earned value chart is visible only to admins and supervisor at the moment
    if (($this->request->session()->read('is_admin') || $this->request->session()->read('is_supervisor')) && $this->request->session()->read('displayCharts')) { ?>  
        <div class="chart">
            <h4>Earned Value Chart</h4>
            <div id="valuewrapper">
                <?php echo $this->Highcharts->render($earnedValueChart, 'valuechart'); ?>
            </div>
        </div>

        <div class="chart">
            <h4>Earned Value Chart 2</h4>
            <div id="valuewrapper2">
                <?php echo $this->Highcharts->render($earnedValueChart2, 'valuechart'); ?>
            </div>
        </div>
    <?php } ?>

    <div class="chart">
        <h4>Phase Chart</h4>
        <div id="phasewrapper">
        	<?php echo $this->Highcharts->render($phaseChart, 'phasechart'); ?>
        </div>
    </div>
    
    <!-- Following two (2) charts are both about requirements, so they share a bigger header -->
    <div class="chart">
        <h4>Requirement Charts</h4>
        <!--<h5>Amounts in numbers</h5>-->
        <div id="reqwrapper">
        	<?php echo $this->Highcharts->render($reqChart, 'reqchart'); ?>
        </div>
    </div>
    </br>
    <div class="chart">
        <!--<h5>Amounts in percentages</h5>-->
        <div id="reqpercentwrapper">
        	<?php echo $this->Highcharts->render($reqPercentChart, 'reqpercentchart'); ?>
        </div>
    </div>
  

    <div class="chart">
        <h4>Commit Chart</h4>
        <div id="commitwrapper">
	        <?php echo $this->Highcharts->render($commitChart, 'commitchart'); ?>
	    </div>
	</div>

    <div class="chart">
        <h4>Test Case Chart</h4>
        <div id="testcasewrapper">
	        <?php echo $this->Highcharts->render($testcaseChart, 'testcasechart'); ?>
        </div>
    </div>

    
    <div class="chart">
        <h4>Working hours</h4>
        <div id="hoursperweekwrapper">
            <?php echo $this->Highcharts->render($hoursPerWeekChart, 'hoursperweekchart')?>
        </div>
    </div>
    </br>
    <div class="chart">
        <div id="totalhourwrapper">
		    <?php echo $this->Highcharts->render($totalhourChart, 'totalhourchart'); ?>
	    </div>
    </div>
    </br>
    <div class="chart">
        <div id="hourswrapper">
		    <?php echo $this->Highcharts->render($hoursChart, 'hourschart'); ?>
	    </div>
    </div>
    </br>
    <div class="chart">
        <div id="hourswrapper2">
		    <?php echo $this->Highcharts->render($hoursChart2, 'hourschart2'); ?>
	    </div>
    </div>
    
    <div class="chart">
        <h4>Risks</h4>
        <div id="risksprobrapper">
            <?php echo $this->Highcharts->render($risksProbChart, 'risksProbChart')?>
        </div>
    </div>
    </br>
    <div class="chart">
        <div id="risksimpactwrapper">
		<?php echo $this->Highcharts->render($risksImpactChart, 'risksImpactChart'); ?>
	</div>
    </div>
    </br>
    <div class="chart">
        <div id="riskscombinedwrapper">
		<?php echo $this->Highcharts->render($risksCombinedChart, 'risksCombinedChart'); ?>
	</div>
    </div>
    
    <?php 
    // The charts for derived metrics is visible only to admins and supervisor
    if ($this->request->session()->read('is_admin') || $this->request->session()->read('is_supervisor')) { ?>  
        <h4>Derived charts for admins and supervisors:</h4>
        </br>
        <div class="chart">
            <div id="hourscomparisonwrapper">
                <?php echo $this->Highcharts->render($hoursComparisonChart, 'hoursComparisonChart'); ?>
            </div>
        </div>
    <?php } ?>
	
</div>
