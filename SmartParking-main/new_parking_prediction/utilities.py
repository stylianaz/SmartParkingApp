import sys

MINUTE = 60
HOUR = 60 * MINUTE
DAY = 24 * HOUR
WEEK = 7 * DAY
YEAR = 365 * DAY


class TimeWrapper(object):
    def __init__(self, seconds_from_epoch):
        self.seconds_from_epoch = seconds_from_epoch

        self._year_progress = (seconds_from_epoch % YEAR) / YEAR
        self._week_progress = (seconds_from_epoch % WEEK) / WEEK
        self._day_progress = (seconds_from_epoch % DAY) / DAY

    def get_progress(self, period: str):
        if period == "year":
            return self._year_progress
        if period == "week":
            return self._week_progress
        if period == "day":
            return self._day_progress


def flush_print(message):
    print(message)
    sys.stdout.flush()
