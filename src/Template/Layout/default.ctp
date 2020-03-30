<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

$cakeDescription = 'MMT';

//debug prints
//print_r($this->request->session()->read('selected_project_role'));
?>

<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
	<meta name="viewport" content="width=700">
    <title>
        <?= $cakeDescription ?>:
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta(
			'tktlogo.png',
			'webroot/img/tkt.png',
			['type' => 'icon'] ); ?>

    <?= $this->Html->css('base.css') ?>
    <?= $this->Html->css('cake.css') ?>

    <?= $this->Html->script(array(
        '//cdnjs.cloudflare.com/ajax/libs/jquery/1.11.2/jquery.min.js',
        '//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.7.0/underscore-min.js'
    )) ?>
    
    <?= $this->Html->script('main.js') ?>
    
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body <?php if(\Cake\Core\Configure::read('debug')){echo 'class="test-layout"';} ?> >
<?php
	$admin = $this->request->session()->read('is_admin');
	$supervisor = ( $this->request->session()->read('selected_project_role') == 'supervisor' ) ? 1 : 0;
	
	// also determine supervisor role by skimming member table. If role supervisor is even once, you'll be set as a supervisor
	$supervisorquery = \Cake\ORM\TableRegistry::get('Members')->find()
						->select(['project_role'])
						->where(['user_id =' => $this->request->session()->read('Auth.User.id'), 'project_role =' => 'supervisor'])
						->first();
	if ( !empty($supervisorquery) ) {
		// set role as a supervisor; the consequence is that 
		// a) comment notifications are visible right after login and b) public statistics links are directly accessible
		$this->request->session()->write('selected_project_role', 'supervisor');
		$supervisor = 1;
	}
        
?>

<!-- general black top naviagation bar -->
<div id="navgeneral">
	<div class="general-links">
		<ul>
			<li class="navBtn" id="projectsBtn"><?= $this->Html->link(__('Projects'), ['controller' => 'Projects', 'action' => 'index']) ?></li>
			<li class="navBtn" id="aboutBtn"><?= $this->Html->link(__('About MMT'), ['controller' => 'Projects', 'action' => 'about']) ?></li>
			<li class="navBtn" id="statsBtn"><?= $this->Html->link(__('Statistics'), ['controller' => 'Projects', 'action' => 'statistics']) ?></li>
			<li class="navBtn" id="faqBtn"><?= $this->Html->link(__('FAQ'), ['controller' => 'Projects', 'action' => 'faq']) ?></li>
			<?php
				if ( empty(!$this->request->session()->read('Auth.User')) && $this->request->session()->check('selected_project')) { ?>
					<li class="navBtn" id="feedbackBtn"><?= $this->Html->link(__('Give feedback'), ['controller' => 'Notes', 'action' => 'add']) ?></li>
				<?php }
			?>

<?php
	if ( empty(!$this->request->session()->read('Auth.User')) ) {
		$name = $this->request->session()->read('Auth.User.first_name') ?>
			<li class="navBtn" id="dropbtn"><a>My links</a>
				
				<div class="dropdown-content">
					<?php
					if (($this->request->session()->read('selected_project_role')) != 'notmember' ) { ?>
						<div id="role"><?=__($this->request->session()->read('selected_project_role')) ?></div> <?php
					}
					?>
					<?= $this->Html->link(__('View Profile'), ['controller' => 'Users', 'action' => 'view', $this->request->session()->read('Auth.User.id')]) ?>
					<?= $this->Html->link(__('Edit Profile'), ['controller' => 'Users', 'action' => 'editprofile']) ?>
					<?= $this->Html->link(__('Log Out'), ['controller' => 'Users', 'action' => 'logout']) ?>
  				</div> <!-- dropdown content ends -->
			</li> <!-- dropdown ends -->
		<?php
			}
			else { ?>
				<li class="navBtn">
					<?= $this->Html->link(__('Log in'), ['controller' => 'Users', 'action' => 'login']) ?>
				</li>
			<?php }
		?>

		</ul>
	</div> <!-- general links end -->
	
</div> <!-- navgeneral ends -->


<div id="area51">
	<!-- This area is meant for notifications about new messages -->
	<?php
	/* Check notifications table; 
	 * if user's member ID is linked to any comment of current project, show a link
	 * admins don't get any notifications
	 */
	// execute only if a project is chosen OR if you are a supervisor
	if ( $this->request->session()->read('selected_project')['id'] || $supervisor ) {
		$userid = $this->request->session()->read('Auth.User.id');
		$projid = $this->request->session()->read('selected_project')['id'];
		
		// non-supervisor users only get notifications from their own project after choosing it
		if ( !($supervisor) ) {
			$memid = Cake\ORM\TableRegistry::get('Members')->find()
						->select(['id'])
						->where(['user_id =' => $userid, 'project_id =' => $projid])
						->toArray();

		// else (as SV) you get informed about every new comment of every project
		} else {
			$memid = Cake\ORM\TableRegistry::get('Members')->find()
						->select(['id'])
						->where(['user_id =' => $userid])
						->toArray();
		}

		// proceed only if ID's were found
		if ( sizeof($memid) > 0) {
			// now try to find current member's id from notifications
			$notifquery = array();
			
			//non-supervisors get only one member id, so one query
			if ( !($supervisor) ) {
			$notifquery = Cake\ORM\TableRegistry::get('Notifications')->find()
						->select(['comment_id', 'weeklyreport_id'])
						->distinct(['weeklyreport_id', 'comment_id'])
						->where(['member_id =' => $memid[0]->id])
						->toArray();
			
			// supervisors need looping to get notifications with all their member ID's
			} else {
				$notifquery = Cake\ORM\TableRegistry::get('Notifications')->find()
							->select(['comment_id', 'weeklyreport_id'])
							->distinct(['weeklyreport_id', 'comment_id']);
				
				// this part fetches all the rows by putting OR condition between all member ID's
				for ($i=0; $i<sizeof($memid); $i++) {
					$notifquery = $notifquery->orWhere(['member_id =' => $memid[$i]->id]);
				}
				$notifquery = $notifquery->toArray();
			}

			// if there are any notifications, tell user
			if ( $amount = sizeof($notifquery) > 0 ) { ?>
				<div id="notificationarea">
					<!-- this button displays a letter icon -->
					<button><span id="label"><?= $this->Html->image('letter.png'); ?></span></button>
					<div class="n-content"><div id="cominfo">
					<?= "Unread comments in:" ?><br/>
					<ul>
						<?php
						
						foreach($notifquery as $notif) {
							// fetch reports' week numbers
							$week = Cake\ORM\TableRegistry::get('Weeklyreports')->find()
										->select(['week', 'project_id'])
										->where(['id =' => $notif->weeklyreport_id])
										->toArray();
							$weekno = $week[0]->week;
							// supervisors also need project's name
							if ( $supervisor ) {
								$projname = \Cake\ORM\TableRegistry::get('Projects')->find()
											->select(['project_name'])
											->where(['id =' => $week[0]->project_id])
											->toArray();
								$projname = $projname[0]->project_name;
							}
							if ( $supervisor ) {
								echo "<li>" . $this->Html->link($projname. " report, week " . $weekno, ['controller'=>'Weeklyreports', 'action'=>'view', $notif->weeklyreport_id]) . "</li>";
							} else {
								echo "<li>" . $this->Html->link("Report, week " . $weekno, ['controller'=>'Weeklyreports', 'action'=>'view', $notif->weeklyreport_id]) . "</li>";
							}
						}

				// close a million tags
				echo "</ul></div></div></div>";
			}
		}
	}
	?>
	
	<!-- this non-breaking space is empty content that makes the page render correctly -->
	&nbsp;
	<!--<div id="topimg">
		<?= $this->Html->image('ylapalkki.jpg'); ?>
	</div>-->
	<div id="topimg">
		<?= $this->Html->image('pitkalogo1.png'); ?>
		<div class="logo">METRICS</div>
		<div class="logo">MONITORING</div>
		<div class="logo">TOOL</div>
	</div>
	<!-- Left side (displays current location)-->
	<nav id="left-title">
		<ul>
			<li class="title-area">
				<h1>
					<?= $project_name = $this->request->session()->read('selected_project')['project_name'];  ?>
				</h1>
			</li>
		</ul>
	</nav> 
	
	<!-- top navigation bar with every other button -->
	
	<!-- Logged user -->
	<?php
	if ( !empty($this->request->session()->read('Auth.User')) ){ ?>
			<?php
			// logged in with a project selected
			if( $this->request->session()->check('selected_project') ) { ?>
			<nav id="navtop" role="navigation" data-topbar>
			<ul>
				<li id="projectViewBtn" class="navbutton"><?= $this->Html->link(__('Project Info'), ['controller' => 'Projects', 'action' => 'view', $this->request->session()->read('selected_project')['id']]) ?></li>
				
				<?php // if not a member, particular links are not shown 
				if ( $this->request->session()->read('selected_project_role') != 'notmember' ) { ?>
					<li id="membersBtn" class="navbutton"><?= $this->Html->link(__('Members'), ['controller' => 'Members', 'action' => 'index']) ?></li>
 					<li id="weeklyreportsBtn" class="navbutton"><?= $this->Html->link(__('Reports'), ['controller' => 'Weeklyreports', 'action' => 'index']) ?></li>
 					<li id="workinghoursBtn" class="navbutton"><?= $this->Html->link(__('Log Time'), ['controller' => 'Workinghours', 'action' => 'index']) ?></li>
 					<li id="risksBtn" class="navbutton"><?= $this->Html->link(__('Risks'), ['controller' => 'Risks', 'action' => 'index']) ?></li>
				<?php } // end if not a member ?>

					<li id="chartsBtn" class="navbutton"><?= $this->Html->link(__('Charts'), ['controller' => 'Charts', 'action' => 'index']) ?></li>
				</ul>
			</nav>
			<?php } // end if logged with a project selected

			else {
				$admin = $this->request->session()->read('is_admin');
				$supervisor = ( $this->request->session()->read('selected_project_role') == 'supervisor' ) ? 1 : 0;
					
				// Get the number of unread feedback for admin
				$unreadNotes = Cake\ORM\TableRegistry::get('Notes')->find()
								->select()
								->where(['note_read IS' => NULL])
								->toArray();
							
				// only admins/supervisors can add new projects
				if($admin || $supervisor) { ?>
				<nav id="navtop" role="navigation" data-topbar>
				<ul>
					<li id="addBtn" class="navbutton"><?= $this->Html->link(__('New Project'), ['controller' => 'Projects','action' => 'add']) ?></li>
				<?php }
				
				if ($admin) { ?>
					<li id="usersBtn" class="navbutton"><?= $this->Html->link(__('Manage Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
 					<li id="metrictypesBtn" class="navbutton"><?= $this->Html->link(__('Metric Types'), ['controller' => 'Metrictypes', 'action' => 'index']) ?> </li>
 					<li id="worktypesBtn" class="navbutton"><?= $this->Html->link(__('Work Types'), ['controller' => 'Worktypes', 'action' => 'index']) ?> </li>
 					<li id="notesBtn" class="navbutton"><?= $this->Html->link(__('All Feedback'), ['controller' => 'Notes', 'action' => 'index']) ?></li> 
 				<?php }
				// link is visible only if there is unread feedback
				if ($admin && (sizeof($unreadNotes)>0)) { ?>
					<li><b><?= $this->Html->link(__('Unread feedback: ' . count($unreadNotes)), ['controller' => 'Notes', 'action' => 'index']) ?> </b></li>
					<?php } ?>
					</ul>
					</nav><?php
				} ?>
				<div class="clearer"></div>
				<?php
 	         	    $title = $this->fetch('title');
 	                echo "<script type='text/javascript'>selectNavButton('$title')</script>"
 	            ?>
	<?php } // end if logged in
	
	else { ?>		
		<div class="clearer"></div>
	<?php } ?>

    <?= $this->Flash->render() ?>
    <section class="container clearfix">
        <?= $this->fetch('content') ?>
    </section>

</div>
<!--<hr/>-->
<footer>
	<!-- "reverse background" for footer -->
	<div id="area52">
		<div class="footerblock">
			<div id="logoblock">
                <?= $this->Html->image('ylapalkki.jpg'); ?>
				<!--<?= $this->Html->image('pitkalogo1.png'); ?>
				<div class="footerlogo">METRICS</div>
				<div class="footerlogo">MONITORING</div>
				<div class="footerlogo">TOOL</div>-->
			</div>
		</div>
		<div class="footerblock">
			<h6>PUBLIC PAGES</h6>
			<ul>
                            <li><?= $this->Html->link(__('Projects'), ['controller' => 'Projects', 'action' => 'index']) ?></li>      
                            <li><?= $this->Html->link(__('About MMT'), ['controller' => 'Projects', 'action' => 'about']) ?> </li>
                            <li><?= $this->Html->link(__('Statistics'), ['controller' => 'Projects', 'action' => 'statistics']) ?> </li>
                            <li><?= $this->Html->link(__('FAQ'), ['controller' => 'Projects', 'action' => 'faq']) ?> </li>                   
			</ul>
		</div>
		<div class="footerblock">
			<h6>OTHER RESOURCES</h6>
			<ul>
                            <!--<li><a href="http://www.uta.fi/sis/tie/pw/statistics.html" target="_blank">Project Work course - Statistics</a></li>
							<li><a href="http://www.uta.fi/sis/yhteystiedot/henkilokunta/pekkamakiaho.html" target="_blank">Supervisor's web site</a></li>-->
							<li><a href="https://coursepages.uta.fi/tiea4/paasivu/statistics/" target="_blank">Project Work course - Statistics</a></li>
							<li><a href="https://www.linkedin.com/in/makiaho/" target="_blank">Supervisor's web site</a></li>            
			</ul>
		</div>
	</div>
</footer>
</body>
</html>
