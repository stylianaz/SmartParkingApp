import datetime
import random

import utilities


# represents one carpark for simulation purposes
class MockCarpark(object):
    def __init__(self, capacity, parking_id, start_timestamp, demand_simulator):
        self.parking_id = parking_id
        self.spaces_available = capacity
        self.current_timestamp = start_timestamp

        self._capacity = capacity
        self._demand_simulator = demand_simulator

        self._time_debt = 0

    @property
    def occupancy(self):
        return self._capacity - self.spaces_available

    def tick(self, dt_seconds):
        satiation_for_whole_period = self._demand_simulator.get_demand_at(utilities.TimeWrapper(self.current_timestamp))

        self._time_debt += dt_seconds
        for _ in range(int(self._time_debt / 60)):
            self.update(satiation_for_whole_period)
        self._time_debt %= 60

        self.current_timestamp += dt_seconds

    # biased random walk - biased by difference from demand satisfaction
    def update(self, satiation_level):
        if not self.momentum_check_passed():
            return

        disequilibrium = satiation_level - self.occupancy
        bias = disequilibrium/self._capacity * 0.8 + 0.5
        bias = max(0.1, bias)
        bias = min(0.9, bias)
        if random.random() >= bias:
            currently_filled = self.occupancy - self.occupancy * 0.02
        else:
            currently_filled = self.occupancy + disequilibrium * 0.02

        if random.random() > currently_filled - int(currently_filled):
            currently_filled = int(currently_filled)
        else:
            currently_filled = int(currently_filled) + 1

        currently_filled = max(0, currently_filled)
        currently_filled = min(self._capacity, currently_filled)

        self.spaces_available = self._capacity - currently_filled

    def momentum_check_passed(self):
        return random.random() > 0.90


class DemandSimulator(object):
    def __init__(self, sources):
        self._sources = sources

    def get_demand_at(self, current_time: utilities.TimeWrapper):
        demand_size = 0
        for source in self._sources:
            if source.currently_active_at(current_time):
                demand_size += source.demandSize
        return demand_size


class DemandSource(object):
    def __init__(self, demand_size, periodicity: str, period_length: datetime.timedelta, period_start=None):
        self.demandSize = demand_size
        self._periodicity = periodicity

        if periodicity == "year":
            denominator = datetime.timedelta(days=365)
        elif periodicity == "week":
            denominator = datetime.timedelta(days=7)
        else:
            denominator = datetime.timedelta(days=1)

        if not period_start:
            self._period_start = random.random()
            self._period_end = self._period_start + period_length / denominator
            return

        self._period_start = period_start / denominator
        self._period_end = (period_start + period_length) / denominator

    def currently_active_at(self, current_time: utilities.TimeWrapper):
        current_progress = current_time.get_progress(self._periodicity)
        return self._period_end > current_progress >= self._period_start
