# Lp2LrsPrivacy ILIAS Plugin

Purpose: Course members can allow or disallow storing learning progress data to a Learning Record Store via a sidebar panel available in courses.
This plugin complements the Lp2Lrs- or Events2Lrs-plugin and restricts the storage of data to objects within courses. Course members decide for themselves whether they agree to the storage of data in LRS. 

This is an OpenSource project by internetlehrer GmbH, Bruchsal.

This project is licensed under the GPL-3.0-only license.

## Requirements

* ILIAS 6.0 - 7.999
* PHP >=7.3
* Installed and activated Lp2Lrs-Plugin or Events2Lrs-Plugin

## Installation

Start at your ILIAS root directory

```bash
mkdir -p Customizing/global/plugins/Services/UIComponent/UserInterfaceHook
cd Customizing/global/plugins/Services/UIComponent/UserInterfaceHook
git clone https://github.com/internetlehrer/Lp2LrsPrivacy.git Lp2LrsPrivacy
```

Make sure you have installed the Lp2Lrs- or the Events2Lrs-Plugin. Then install/update and activate the Lp2LrsPrivacy-Plugin in the ILIAS Plugin Administration. 

## Usage

### Frontend

In the right sidebar of courses you can see an expandable panel with the following title and status (default status: disabled):

> Learning process storage *disabled*

The panel body contains the following elements:

- a toggle switch to enable / disable learning process storage
- a message text that can be customized via language variables
- Information about the LRS and authentication. These include:
  - Learning Record Store (LRS)
  - User identification for resource
  - Username

Clicking the toggle button enables/disables the privacy settings for Learning statistics.

### Backend

In the plugin administration, you can use the "Configure" plugin action to view the approval changes for each user and course over time. The list can be filtered by user and sorted by the following columns:

- Date
- User
- Status
- Course
