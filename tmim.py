# THE MOST INTERESTING MAN IN THE WORLD
# SLACK CHATBOT IN PYTHON
#
# Author: Zachary Gillis
#--------------------------------------

import os
import time
import re
from slackclient import SlackClient
from config import SLACK_BOT_TOKEN
from api_calls import getBTCPrice

# Instantiate Slack Client
slack_client = SlackClient(SLACK_BOT_TOKEN)
starterbot_id = None

# Constants
RTM_READ_DELAY = 1 # 1-second delay RTM read
EXAMPLE_COMMAND = "hello"
MENTION_REGEX = "^<@(|[WU].+?)>(.*)"


def parse_bot_commands(slack_events):
    """
    Parses a list of events coming from the Slack RTM API to find bot commands.
    If a bot command is found, this function returns a tuple of command and channel.
    If its not found, then this function returns None, None.
    """
    for event in slack_events:
        if event['type'] == "message" and not "subtype" in event:
            user_id, message = parse_direct_mention(event['text'])
            if user_id == starterbot_id:
                sender_id = event['user']
                return message, sender_id, event['channel']
    return None, None, None

def parse_direct_mention(message_text):
    """
    Finds a direct mention (a mention that is at the beginning) in message text
    and returns the user ID which was mentioned. If there is no direct mention, returns None
    """
    matches = re.search(MENTION_REGEX, message_text)
    # the first group contains the username, the second group contains the remaining message
    return (matches.group(1), matches.group(2).strip()) if matches else (None, None)

def handle_command(command, channel, sender_id):
    # Executes bot command if command is known.
    # Default response is help text for the user
    default_response = "I don't always understand people, but when I do, they don't speak gibberish like you. Shotgun a Dos Equis or five and get back to me. Try *{}*.".format(EXAMPLE_COMMAND)

    # Finds and executes given command, filling in response
    response = None
    # Implement more commands here.

    if command.startswith(EXAMPLE_COMMAND):
        response = "Hi there %s. Stay thirsty my friend." % ("<@" + sender_id + ">")
    elif command.startswith("about"):
        response = "I'm a Slack chatbot written in Python. I don't always crash, but when I do, call Zach."
    elif command.startswith("bitcoin"):
        btc_price = getBTCPrice()
        response = "The current price of Bitcoin is $%.2f USD." % btc_price

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

        while True:
            command, user_id, channel = parse_bot_commands(slack_client.rtm_read())
            if command:
                handle_command(command, channel, user_id)
            time.sleep(RTM_READ_DELAY)

    else:
        print("Connection failed.")



