04/10/2014 10:08:33
-------------------------------------------
How to install on a moodle 2.x site.
-------------------------------------------
Method One
1.- Visit your Moodle site's "site administration -> plugins -> install plugins" page, 
2.- Choose "Activity Module(mod)" and drag the quizletimport.zip folder into the "ZIP Package" area.
3.- Follow the on screen instructions to complete the installation 
NB If the "mod" folder does not have the correct permissions you will see a warning message
and will need to change the permissions, or use Method Two

Method Two
1.- Unzip the quizletimport.zip archive to your local computer.
2.- This should give you a folder named "quizletimport".
3.- Upload the "quizletimport" folder to your [moodle site]/mod folder using FTP or CPanel.
4.- Visit your Admin/Notifications page so that the module gets installed. 

For both methods, at the end of the installation process, the plugin configuration settings will be displayed.
These are explained below. They may be completed at this point, or at any time, by visiting the plugin settings page.
They may actually be easier to fill in via the plugin settings page.

--------------------------------------------------------------
Configuring Quizletmport Module for Moodle 2.x
--------------------------------------------------------------

Plugin Settings for QuizletImport Module 
***********************************************
The settings for the QuizletImport module can be found at:
[Moodle Site]/Site Administration -> Plugins -> Activity Modules -> Quizlet Import

The most important of the settings are the 2 API keys from Quizlet.com. 
You need to make these over at quizlet.com once you are logged in there. They are free.
i)  Go to https://quizlet.com/api-dashboard
ii) Create an "application", and call it PoodLL (or anything you like).
iii) For the redirect URL of your site, just use the base URL of your Moodle site. eg.
http://mysite.com/moodle or http://moodle.mysite.com
iii) Copy and paste both keys into the settings pages for the mod.
You can only make one "application" per quizlet account. It is only used when creating quizlet activities or import xml files.
Your students will not need to login to quizlet or be aware of any of this. You only need one set of keys per Moodle site.
You will use the same keys for the QuizletImport module and the QuizletQuiz block.

The remaining settings are the default width and heights of each of the activity types.

How to Add a QuizletImport Activity to a Course
***********************************************
Go into Edit mode and from the "Add an Activity/Resource" popup, choose to add a QuizletImport mod.

* Give your activity a name, and a description(optional). 

*Specify the Quizlet set ID. It is possible to do this manually, bu probably easier to click on "select a set," authorize with Quizlet, and search for a set.
When you have selected a set, you will be returned to the settings page.
Hint: It makes sense to do this step before entering a name and description, since these will be lost when returning from Quizlet if unsaved. 

* Specify the Quizlet Title. This is just to help your remember the quizlet set. You can enter anything here.

* Activity Type. This can be one of flashcards, scatter, spacerace, test, speller or learn.

* Minimum time required. This is the minimum time before the activity will be triggered as complete. It is ignored if completion is not set.
If set to 0, the default for the activity type will be taken from the settings page. This is useful for changing the minimum time for all activities of a type.

* Show countdown to completion. If set a countdown timer will be displayed to the student, telling them how long they must remain on the page before the activity is complete.

* Show completion label when complete. If set, a message will be shown on screen when the activity is complete.

NB To enable the completion features, you must ensure the following settings are in place
i)  the site at site admin -> advanced features -> enable completion tracking
ii) the course at course administration -> edit settings ->completion tracking ->enable completion
iii) in the settings for *each* QuizletImport activity you must set in the "Activity Completion" area
a) Completion Tracking = "Show activity as complete when conditions are met."
b) Require View = True (ie check the checkbox)

In the QuizletImport mod we use the RequireView condition, but we alter it so that the minimum time on page must be met before the condition is fulfilled. 
A simple view will not complete the activity.