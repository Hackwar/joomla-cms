INSERT INTO "#__mail_templates" ("template_id", "subject", "body", "params") VALUES
('joomla.updatenotification', 'PLG_SYSTEM_UPDATENOTIFICATION_EMAIL_SUBJECT', 'PLG_SYSTEM_UPDATENOTIFICATION_EMAIL_BODY', '{"tags":["newversion","curversion","sitename","url","link","releasenews"]}'),
('com_contact.mail', 'COM_CONTACT_ENQUIRY_SUBJECT', 'COM_CONTACT_ENQUIRY_TEXT', '{"tags":["sitename","name","email","subject","body","url","customfields"]}'),
('com_contact.mail.copy', 'COM_CONTACT_COPYSUBJECT_OF', 'COM_CONTACT_COPYTEXT_OF', '{"tags":["sitename","name","email","subject","body","url","customfields"]}'),
('com_users.massmail.mail', 'COM_USERS_MASSMAIL_MAIL_SUBJECT', 'COM_USERS_MASSMAIL_MAIL_BODY', '{"tags":["subject","body","subjectprefix","bodysuffix"]}'),
('com_users.administration.new_user.user', 'PLG_USER_JOOMLA_NEW_USER_EMAIL_SUBJECT', 'PLG_USER_JOOMLA_NEW_USER_EMAIL_BODY', '{"tags":["name","sitename","url","username","password","email"]}');
