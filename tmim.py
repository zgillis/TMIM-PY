# THE MOST INTERESTING MAN IN THE WORLD
# SLACK CHATBOT IN PYTHON
#
# Author: Zachary Gillis
#--------------------------------------

import os
import time
import re
from slackclient import SlackClient
from database import TMIMDatabase
from config import SLACK_BOT_TOKEN
from api_calls import getBTCPrice

# Instantiate Slack Client
slack_client = SlackClient(SLACK_BOT_TOKEN)
starterbot_id = None

# Database access
db = None

# Constants
RTM_READ_DELAY = 1 # 1-second delay RTM read
MENTION_REGEX = "^<@(|[WU].+?)>(.*)"
HELP_TEXT = """
Basic Commands:
        *help* - find a list of commands
        *me* - see if you are registered
        *id* - see your Slack user id
        *hello* - say hi to me
        *about* - learn about this bot  
Feature Commands:
        *bitcoin* - get the current Bitcoin price in USD
  
"""


def parse_bot_commands(slack_events):
    for event in slack_events:
        if event['type'] == "message" and not "subtype" in event:
            user_id, message = parse_direct_mention(event['text'])
            if user_id == starterbot_id:
                sender_id = event['user']
                return message, sender_id, event['channel']
    return None, None, None


def parse_direct_mention(message_text):
    matches = re.search(MENTION_REGEX, message_text)
    # the first group contains the username, the second group contains the remaining message
    return (matches.group(1), matches.group(2).strip()) if matches else (None, None)


def handle_command(command, channel, sender_id):
    default_response = """I don't always understand people, but when I do, they don't speak gibberish like you. 
Shotgun a Dos Equis or five and get back to me. Try *{}* to see what I can do.""".format("help")

    # Finds and executes given command, filling in response
    command = command.lower()
    response = None

    # COMMAND HANDLING
    if command.startswith("hi") or command.startswith("hello"):
        response = "Hi there %s. Stay thirsty my friend." % ("<@" + sender_id + ">")
    elif command.startswith("help"):
        response = HELP_TEXT
    elif command.startswith("about"):
        response = "I'm a Slack chatbot written in Python. I don't always crash, but when I do, call Zach."
    elif command.startswith("bitcoin") or command.startswith("btc"):
        btc_price = getBTCPrice()
        response = "The current price of Bitcoin is $%.2f USD." % btc_price
    elif command.startswith('id'):
        response = "Your user ID is: %s." % sender_id
    elif command.startswith("register"):
        user = db.get_user(sender_id)
        if user is None:
            response = "I don't have you registered yet. Please register with *register [first_name] [last_name]*."
        else:
            response = "Hi, you're %s %s. You have %s likes. Stay thirsty my friend." % (user.first_name, user.last_name, user.like_bal)
    elif command.startswith("register"):
        response = "Not implemented."



    # Sends response back to channel.
    slack_client.api_call(
        "chat.postMessage",
        channel=channel,
        as_user=True,
        text=response or default_response
    )


if __name__ == "__main__":
    if slack_client.rtm_connect(with_team_state=False):
        print("Starter bot connected and running!")

        # Read bot's user ID by calling Web API method `auth.test`
        starterbot_id = slack_client.api_call("auth.test")["user_id"]

        db = TMIMDatabase()

        while True:
            command, user_id, channel = parse_bot_commands(slack_client.rtm_read())
            if command:
                handle_command(command, channel, user_id)
            time.sleep(RTM_READ_DELAY)

    else:
        print("Connection failed.")



