<?php
$wp_users = $this->db->users;

$this->tableDefinitions = array (
	"course"=> "(
		id int(11) NOT NULL AUTO_INCREMENT,
		 course_number varchar(45) DEFAULT NULL,
		 last_update date DEFAULT NULL,
		 title varchar(255) NOT NULL,
		 description text,
		 url varchar(255) DEFAULT NULL,
		 record_status tinyint(4) NOT NULL DEFAULT '1',
		 PRIMARY KEY  (id)
		) ENGINE=InnoDB",

	"organization"=> "(
		id int(11) NOT NULL AUTO_INCREMENT,
		 name varchar(100) NOT NULL,
		 record_status tinyint(3) unsigned DEFAULT '1',
		 PRIMARY KEY  (id)
		) ENGINE=InnoDB",

	"subscription_code"=> "(
		id int(11) NOT NULL AUTO_INCREMENT,
		 code varchar(45) NOT NULL,
		 organization_id int(11) NOT NULL,
		 expiration_date date DEFAULT NULL,
		 user_limit int(10) unsigned DEFAULT NULL,
		 record_status tinyint(3) unsigned DEFAULT '1',
		 PRIMARY KEY  (id),
		 UNIQUE KEY #subscription_code_unique_code# (code),
		 KEY #subscription_code_organization# (organization_id),
		 KEY #subscription_code_code# (code),
		 CONSTRAINT #subscription_code_organization# FOREIGN KEY (organization_id) REFERENCES #organization# (id) ON DELETE CASCADE ON UPDATE CASCADE
		) ENGINE=InnoDB",


	"user"=> "(
		id bigint(20) unsigned NOT NULL,
		 organization_id int(11) DEFAULT NULL,
		 subscription_code_id int(11) DEFAULT NULL,
		 user_name varchar(60) NOT NULL,
		 email varchar(60) DEFAULT NULL,
		 first_name varchar(60) DEFAULT NULL,
		 last_name varchar(60) DEFAULT NULL,
		 middle_initial varchar(60) DEFAULT NULL,
		 address_1 varchar(60) DEFAULT NULL,
		 address_2 varchar(60) DEFAULT NULL,
		 city varchar(60) DEFAULT NULL,
		 state varchar(60) DEFAULT NULL,
		 zip varchar(20) DEFAULT NULL,
		 country varchar(60) DEFAULT NULL,
		 title varchar(60) DEFAULT NULL,
		 role varchar(45) DEFAULT NULL,
		 phone varchar(60) DEFAULT NULL,
		 assigned_org varchar(255),
		 import_subscription_code int(11),
		 PRIMARY KEY  (id),
		 KEY #user_organization# (organization_id),
		 KEY #user_subscription_code# (subscription_code_id),
		 CONSTRAINT #user_ibfk_1# FOREIGN KEY (id) REFERENCES $wp_users (ID) ON DELETE NO ACTION ON UPDATE CASCADE,
		 CONSTRAINT #user_organization# FOREIGN KEY (organization_id) REFERENCES #organization# (id) ON DELETE SET NULL ON UPDATE CASCADE,
		 CONSTRAINT #user_subscription_code# FOREIGN KEY (subscription_code_id) REFERENCES #subscription_code# (id) ON DELETE SET NULL ON UPDATE CASCADE
		) ENGINE=InnoDB",



	"subscription_code_course"=> "(
		id int(11) NOT NULL AUTO_INCREMENT,
		 subscription_code_id int(11) NOT NULL,
		 course_id int(11) NOT NULL,
		 subscription_period_number int(10) unsigned DEFAULT NULL,
		 subscription_period_interval varchar(45) DEFAULT NULL,
		 PRIMARY KEY  (id),
		 UNIQUE KEY unique_subscription_code_course (subscription_code_id, course_id),
		 KEY #subscription_code_course_code# (subscription_code_id),
		 KEY #subscription_code_course_course# (course_id),
		 CONSTRAINT #subscription_code_course_code# FOREIGN KEY (subscription_code_id) REFERENCES #subscription_code# (id) ON DELETE CASCADE ON UPDATE CASCADE,
		 CONSTRAINT #subscription_code_course_course# FOREIGN KEY (course_id) REFERENCES #course# (id) ON DELETE CASCADE ON UPDATE CASCADE
		) ENGINE=InnoDB",

	"ecommerce"=>"(
		  id int(11) NOT NULL AUTO_INCREMENT,
		  user_id bigint(20) unsigned NOT NULL,
		  transaction_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  transaction_id varchar(45) NOT NULL,
		  transaction_amount decimal(8,2) NOT NULL,
		  PRIMARY KEY (id),
		  UNIQUE KEY transaction_id_UNIQUE (transaction_id),
		  KEY #ec_user_idx# (user_id),
		  CONSTRAINT #ec_user# FOREIGN KEY (user_id) REFERENCES #user# (id) ON UPDATE CASCADE ON DELETE NO ACTION
		) ENGINE=InnoDB",
	
	"ecommerce_item"=>"(
		  id int(11) NOT NULL AUTO_INCREMENT,
		  ecommerce_id int(11) NOT NULL,
		  course_id int(11) NOT NULL,
		  course_price decimal(8,2) NOT NULL,
		  PRIMARY KEY (id),
		  KEY #ec_item_ecommerce_idx# (ecommerce_id),
		  KEY #ec_item_course_idx# (course_id),
		  CONSTRAINT #ec_item_course# FOREIGN KEY (course_id) REFERENCES #course# (id) ON UPDATE CASCADE,
		  CONSTRAINT #ec_item_ecommerce# FOREIGN KEY (ecommerce_id) REFERENCES #ecommerce# (id) ON UPDATE CASCADE
		) ENGINE=InnoDB",

	"user_course_registration"=> "(
		id int(11) NOT NULL AUTO_INCREMENT,
		 course_id int(11) NOT NULL DEFAULT '0',
		 user_id bigint(20) unsigned NOT NULL DEFAULT '0',
		 registration_date datetime DEFAULT NULL,
		 registration_type int(11) NOT NULL DEFAULT '1',
		 ec_item_id int(11) DEFAULT NULL,
		 expiration_date date DEFAULT NULL,
		 course_status varchar(45) DEFAULT NULL,
		 course_completion_date date DEFAULT NULL,
		 score int(11) DEFAULT NULL,
		 scormdata text,
		 record_status tinyint(4) NOT NULL DEFAULT '1',
		 PRIMARY KEY  (id),
		 UNIQUE KEY #unique_user_course# (course_id,user_id),
		 KEY #user_course_registration_course# (course_id),
		 KEY #user_id# (user_id),
		 KEY #ucr_ec_item_idx# (ec_item_id),
		 CONSTRAINT #user_course_registration_ibfk_1# FOREIGN KEY (user_id) REFERENCES $wp_users (ID) ON DELETE CASCADE ON UPDATE CASCADE,
		 CONSTRAINT #user_course_registration_course# FOREIGN KEY (course_id) REFERENCES #course# (id) ON DELETE CASCADE ON UPDATE CASCADE,
		 CONSTRAINT #ucr_ec_item# FOREIGN KEY (ec_item_id) REFERENCES #ecommerce_item# (id) ON DELETE RESTRICT ON UPDATE CASCADE
		) ENGINE=InnoDB",

	"user_course_assessment_response"=> "(
		id int(11) NOT NULL AUTO_INCREMENT,
		 user_id bigint(20) unsigned NOT NULL,
		 course_id int(11) NOT NULL,
		 name varchar(45) DEFAULT NULL,
		 attempt int(11) DEFAULT NULL,
		 score int(11) DEFAULT NULL,
		 result varchar(45) DEFAULT NULL,
		 response_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		 q1_response varchar(45) DEFAULT NULL,
		 q2_response varchar(45) DEFAULT NULL,
		 q3_response varchar(45) DEFAULT NULL,
		 q4_response varchar(45) DEFAULT NULL,
		 q5_response varchar(45) DEFAULT NULL,
		 q6_response varchar(45) DEFAULT NULL,
		 q7_response varchar(45) DEFAULT NULL,
		 q8_response varchar(45) DEFAULT NULL,
		 q9_response varchar(45) DEFAULT NULL,
		 q10_response varchar(45) DEFAULT NULL,
		 q11_response varchar(45) DEFAULT NULL,
		 q12_response varchar(45) DEFAULT NULL,
		 q13_response varchar(45) DEFAULT NULL,
		 q14_response varchar(45) DEFAULT NULL,
		 q15_response varchar(45) DEFAULT NULL,
		 q1_result tinyint(4) DEFAULT NULL,
		 q2_result tinyint(4) DEFAULT NULL,
		 q3_result tinyint(4) DEFAULT NULL,
		 q4_result tinyint(4) DEFAULT NULL,
		 q5_result tinyint(4) DEFAULT NULL,
		 q6_result tinyint(4) DEFAULT NULL,
		 q7_result tinyint(4) DEFAULT NULL,
		 q8_result tinyint(4) DEFAULT NULL,
		 q9_result tinyint(4) DEFAULT NULL,
		 q10_result tinyint(4) DEFAULT NULL,
		 q11_result tinyint(4) DEFAULT NULL,
		 q12_result tinyint(4) DEFAULT NULL,
		 q13_result tinyint(4) DEFAULT NULL,
		 q14_result tinyint(4) DEFAULT NULL,
		 q15_result tinyint(4) DEFAULT NULL,
		 PRIMARY KEY  (id),
		 UNIQUE KEY #user_course_assessment_attempt_unique# (user_id,course_id,name,attempt),
		 KEY #ucar_user# (user_id),
		 KEY #ucar_course# (course_id),
		 CONSTRAINT #ucar_course# FOREIGN KEY (course_id) REFERENCES #course# (id) ON DELETE CASCADE ON UPDATE CASCADE,
		 CONSTRAINT #ucar_user# FOREIGN KEY (user_id) REFERENCES #user# (id) ON DELETE CASCADE ON UPDATE CASCADE
		) ENGINE=InnoDB",

	"user_course_event"=> "(
		id int(11) NOT NULL AUTO_INCREMENT,
		 user_id bigint(20) unsigned NOT NULL,
		 course_id int(11) NOT NULL,
		 event_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		 event_type varchar(45) NOT NULL,
		 PRIMARY KEY  (id),
		 KEY #uce_user# (user_id),
		 KEY #uce_course# (course_id),
		 KEY #uce_event_type# (event_type),
		 CONSTRAINT #uce_course# FOREIGN KEY (course_id) REFERENCES #course# (id) ON DELETE CASCADE ON UPDATE CASCADE,
		 CONSTRAINT #uce_user# FOREIGN KEY (user_id) REFERENCES #user# (id) ON DELETE CASCADE ON UPDATE CASCADE
		) ENGINE=InnoDB",

	"course_assessment"=> "(
		id int(11) NOT NULL AUTO_INCREMENT,
		 course_id int(11) NOT NULL,
		 name varchar(45) NOT NULL,
		 PRIMARY KEY  (id),
		 KEY #course_assessment_ref_course# (course_id),
		 CONSTRAINT #course_assessment_ref_course# FOREIGN KEY (course_id) REFERENCES #course# (id) ON DELETE CASCADE ON UPDATE CASCADE
		) ENGINE=InnoDB",

	"course_assessment_question"=> "(
		id int(11) NOT NULL AUTO_INCREMENT,
		 course_assessment_id int(11) NOT NULL,
		 sequence int(11) NOT NULL,
		 correct_answer int(11) NOT NULL,
		 text text NOT NULL,
		 PRIMARY KEY  (id),
		 UNIQUE KEY #unique_question_assessment_sequence# (course_assessment_id,sequence),
		 KEY #question_assessment# (course_assessment_id),
		 CONSTRAINT #question_assessment# FOREIGN KEY (course_assessment_id) REFERENCES #course_assessment# (id) ON DELETE CASCADE ON UPDATE CASCADE
		) ENGINE=InnoDB",

	"course_assessment_answer"=> "(
		id int(11) NOT NULL AUTO_INCREMENT,
		 question_id int(11) NOT NULL,
		 sequence int(11) NOT NULL,
		 text text NOT NULL,
		 PRIMARY KEY  (id),
		 UNIQUE KEY #unique_question_answer_sequence# (question_id,sequence),
		 KEY #assessment_answer_question# (question_id),
		 CONSTRAINT #assessment_answer_question# FOREIGN KEY (question_id) REFERENCES #course_assessment_question# (id) ON DELETE CASCADE ON UPDATE CASCADE
		) ENGINE=InnoDB",

	"announcement"=> "(
		id int(11) NOT NULL AUTO_INCREMENT,
		 create_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		 created_by varchar(45) NOT NULL,
		 title varchar(45) NOT NULL,
		 text text NOT NULL,
		 PRIMARY KEY  (id)
		) ENGINE=InnoDB"
);
?>