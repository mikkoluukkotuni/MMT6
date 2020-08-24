

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
	See also <?= $this->Html->link(__('publications'), ['controller' => 'Projects', 'action' => 'publications']) ?> related to MMT.
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
        <h5>Version 6.2 (24.8.2020)</h5>
        <ul>
            <li>New work types and metric types added (including project's degree of readiness and overall status metrics)</li>
            <li>Earned value method method implemented (data and charts visible only to supervisors)</li>
            <li>Pie chart added displaying projects working hours categorized by type</li>
            <li>New metrics table and some new data added to statistics page (only visible to supervisors)</li>
            <li>Usability updates and bug fixes: new tutorial videos, better instructions for forms, small layout updates</li>
        </ul>
        <br>
    </p>
    <p>
        <h5>Version 6.1 (4.4.2020)</h5>
        <ul>
            <li>GitHub connection that can fetch number of commits for weekly report</li>
            <li>Predictive chart for project's total hours</li>
            <li>Information of the weekly report creator and updater displayed in weekly report</li>
            <li>Usability updates: target hour marker for the member's personal working hours chart, display metric name when updating, better error messages for weekly report forms, group project's connection settings into project's info page</li>
        </ul>
        <br>
    </p>
    <p>
        <h5>Version 6.0 (16.3.2020)</h5>
        <ul>
            <li>Increase of the mobile usability</li>
            <li>Last activity of a project  member shown</li>
            <li>Personal working hours chart and prediction of the total hours during the project</li>
            <li>Bugs fixed: HTTPS-redirection issues, diagram visibility issues for supervisors, working hours diagrams issues</li>
            <li>Coming soon: linking to version control systems, usability improvements, prediction for the project…</li>
        </ul>

    </p>
    <h4>Previous versions</h4>
	<p>
	    Version 5.0 were implemented during the fall term of 2019 as a coursework for TIEA4 Project Work course and TIETS19 Software Project Management course. 
            The team consisted of two project managers (Hanna-Riikka Rantamaa and Henna Lehto) 
            and four developers (Kimi af Forselles, Mikko Luukko, Tommi Piili and Ville Niemi). 
            Updates included: new interface with TUNI-Theme (logo and brand of the new Tampere University), 
            new diagrams like comparing the total hours of the project to the all parallel public projects, 
            HTTPS protocol, bug fixing and other smaller features.
	</p>
        <p>
            Version 4.0 was implemented by Murat Pojon as a TIETS16 programming project 
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
