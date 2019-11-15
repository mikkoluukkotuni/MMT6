
    <ul class="side-nav">
        <li><?= $this->Html->link(__('Public statistics'), ['controller' => 'Projects', 'action' => 'statistics']) ?> </li>
        <li><?= $this->Html->link(__('FAQ'), ['controller' => 'Projects', 'action' => 'faq']) ?> </li>
    </ul>    


<div id="faq" class="projects index large-9 medium-18 columns content float: left">
    <h3><?= __('About MMT') ?></h3>

	
	 <h4>MMT</h4>
    <p>
  
	Metrics Monitoring Tool is part of Pekka Mäkiaho's PhD work. MMT is in use at Tampere University's courses: Project work and Software Project management.
	
	<br>
	It is used for logging working hours in a project, for managing a project and reporting its state, and for observing one project or the whole portfolio visually.
	<br>
	
		
	<br>
	
	<!-- See also <a href="http://metricsmonitoring.sis.uta.fi/publications" target="_blank">publications</a> related to MMT -->
	See also <?= $this->Html->link(__('publications'), ['controller' => 'Projects', 'action' => 'publications']) ?> related to MMT
    </p>
	
	
	
	
	
    <h4>Test environment</h4>
    <p>
  
	You can view  all the public projects at the production site with 
	<br>
	username/password:  guest@guest.com/guestguest
	
	<br>
	<br>
	
	You can also visit the <a href="http://mmttest.sis.uta.fi" target="_blank">test environment</a>.
	
	<br>
	<br>
	On the test environment, you can use the next user accounts:
	<br>
	Username: test@test.com
	Password: testtest
	<br>
	Username: manager@m.com
	Password: managermanager
	
	<br>
	
	If you want more information and/or create your own projects, just send e-mail to pekka.makiaho@uta.fi
	
	See also <a href="http://metricsmonitoring.sis.uta.fi/publications" target="_blank">publications</a> related to MMT
	
    </p>
    <h4>Release notes</h4>
    <p>
        <h5>Version 4.0 (May 2017)</h5>
        <p>Mobile Interface</p>
        <p>Risk metric</p>
        <p>Trello and Slack Integrations</p>
        <p>Comment edit functionality</p>
        <p>Reset forgotten passwords</p>
    </p>
    <h4>Previous versions</h4>
        <p>
            Version 4.0 were implemented by Murat Pojon as a TIETS16 programming project 
            during the spring term of 2017. 
        </p>
        <p>
            Versions 2.0-2.1 and version 3.0 were implemented by Sirkku Seitamäki as a TIETS16 programming project 
            during the summer and fall terms of 2016. 
        </p>
        <p>
            Versions 1.1-1.3 were implemented during the spring term of 2016 as a coursework for 
            TIEA4 Project Work course and TIETS19 Software Project Management course. 
            The team consisted of two project managers (Elena Solovieva and Choudhary Shahzad Shabbir) 
            and two developers (Andreas Valjakka and Sirkku Seitamäki). 
        </p>   
        <p>
            Version 1.0 was the product of the fall 2015 Project Work team.  
            Jukka Ala-Fossi and Mykola Andrushchenko were the developers in the project and 
            Katriina Löytty was the manager. This was the first version taken to production.
        </p>    
		<p>
            Version 0.9 was developed during  the academic year 2014-2015. However, after the testing and evaluation, it was never taken to use.
			Even if the coding of the next version was started "from the scratch", a lot of ideas were gathered and implemented during the project.
            
        </p>  

</div>
