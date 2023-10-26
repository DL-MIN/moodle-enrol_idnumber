# Enrol by Course ID number

A Moodle enrolment plugin to map the users' custom profile field with multiple entries against the course ID number.

This allows external student management systems to be used to automatically enrol students without an API. Teachers simply need to set the corresponding ID number in the course configuration.


## Installation

### Install with git

1. Use a command line interface of your choice on the target system.
1. Change to the Moodle **enrol** directory: `cd /path/to/moodle/enrol/`
1. `git clone https://github.com/DL-MIN/moodle-enrol_idnumber`
1. Navigate on your Moodle Webinterface and follow the migration process.


### Install from zip

1. Download zip file from GitHub: hhttps://github.com/DL-MIN/moodle-enrol_idnumber/archive/main.zip
1. Unpack zip file to `/path/to/moodle/enrol/`
1. Navigate on your Moodle Webinterface and follow the migration process.


## Usage

1. Create a custom profile field in *Users* → *User profile fields* to be populated by your authentication plugin.
1. In the plugin preferences, select the profile field along with a delimiter used by your source system.
1. This enrolment plugin can be inserted and enabled by default in new courses.


## License

[GNU GPLv3](https://choosealicense.com/licenses/gpl-3.0/)
