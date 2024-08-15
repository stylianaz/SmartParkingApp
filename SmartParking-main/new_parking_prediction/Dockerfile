FROM python:3

WORKDIR /ml_pipeline

RUN apt-get update \
 && apt-get install -y default-mysql-client

COPY requirements.txt ./
RUN pip install --no-cache-dir -r requirements.txt