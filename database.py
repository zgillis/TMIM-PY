# THE MOST INTERESTING MAN IN THE WORLD
# Author: Zachary Gillis
#
# DATABASE ACCESS LAYER

import mysql.connector
from config import db_config


class TMIMDatabase:
    con = None

    def __init__(self):
        print("Connecting to database..")
        self.con = mysql.connector.connect(
            host=db_config['host'],
            user=db_config['user'],
            passwd=db_config['passwd'],
            database=db_config['database']
        )
        print("Connected to database.")

    def get_user(self, user_id):
        user = None
        cursor = self.con.cursor()
        sql = "SELECT * FROM users WHERE UID = %s"
        cursor.execute(sql, (user_id,))
        rs_user = cursor.fetchone()
        if rs_user is not None:
            user = User(rs_user[0], rs_user[1], rs_user[2], rs_user[3], rs_user[4])
        return user

    def create_user(self, uid, fn, ln):
        cursor = self.con.cursor()
        sql = "INSERT INTO users(UID, first_name, last_name) VALUES(%s, %s, %s)"
        cursor.execute(sql % (uid, fn, ln))


class User:
    UID = None
    first_name = None
    last_name = None
    pwr_lvl = None
    like_bal = None

    def __init__(self, uid, fn, ln, pwr_lvl, like_bal):
        self.UID = uid
        self.first_name = fn
        self.last_name = ln
        self.pwr_lvl = pwr_lvl
        self.like_bal = like_bal
