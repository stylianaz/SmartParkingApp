import mysql.connector


def get_connection():
    mydb = mysql.connector.connect(
        host="mariadb",
        user="smart_parker",
        passwd="smartParking",
        database="smartParking",
        auth_plugin="mysql_native_password"
    )
    return mydb
