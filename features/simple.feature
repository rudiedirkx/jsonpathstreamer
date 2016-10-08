Feature: Simple parsing

	Background: JSON
		Given I have a JSON file
			"""
			{
				"users": [
					{"name": "Jeff", "mood": 4, "comments": [
						{"mood": 5, "body": "Great stuff"},
						{"mood": 1.5, "body": "Weak!"},
					]},
					{"name": "Mary", "mood": 3.5, "comments": [
						{"mood": 2.5, "body": "Doesn't work..."},
						{"mood": 2.5, "body": "Still doesn't work..."}
					]},
					{"name": "Nicole", "mood": 4.5, "comments": [
						{"mood": 1, "body": "Is crap"},
						{"mood": 5, "body": "Is the best!"},
						{"mood": 5, "body": "OMG OMG OMG!"},
					]},
					{"name": "Jim", "mood": 3.5, "comments": [
						{"mood": 3, "body": "I could do better..."}
					]}
				]
			}
			"""

	Scenario: Return users' names
		Given I have a listener
			"""
			class Listener extends \rdx\jsonpathstreamer\RegexConfigJsonListener {
				public function getRules() {
					return [
						'#^users/\d+/name$#',
					];
				}
			}
			"""
		When I run the streamer
		Then I should have
			"""
			{
				"users": [
					{"name": "Jeff"},
					{"name": "Mary"},
					{"name": "Nicole"},
					{"name": "Jim"}
				]
			}
			"""

	Scenario: Return users' comments' moods
		Given I have a listener
			"""
			class Listener extends \rdx\jsonpathstreamer\RegexConfigJsonListener {
				public function getRules() {
					return [
						'#^users/\d+/name$#',
						'#^users/\d+/comments/\d+/mood$#',
					];
				}
			}
			"""
		When I run the streamer
		Then I should have
			"""
			{
				"users": [
					{
						"name": "Jeff",
						"comments": [
							{"mood": 5},
							{"mood": 1.5}
						]
					},
					{
						"name": "Mary",
						"comments": [
							{"mood": 2.5},
							{"mood": 2.5}
						]
					},
					{
						"name": "Nicole",
						"comments": [
							{"mood": 1},
							{"mood": 5},
							{"mood": 5}
						]
					},
					{
						"name": "Jim",
						"comments": [
							{"mood": 3}
						]
					}
				]
			}
			"""
