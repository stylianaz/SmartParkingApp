FROM bitnami/laravel:5.6.21

WORKDIR /smart_parking

RUN sudo apt-get update \
 && sudo apt-get install -y mysql-client \
 && sudo apt-get install -y git

COPY package.json package-lock.json ./
RUN npm install

# python build prerequisites
RUN sudo apt-get install -y build-essential checkinstall libreadline-gplv2-dev libncursesw5-dev libsqlite3-dev tk-dev libgdbm-dev libc6-dev libbz2-dev libffi-dev

# have to build openssl from source because the version packages with debian jessie is misisng a header
RUN cd /usr/src \
 && curl https://www.openssl.org/source/openssl-1.0.2o.tar.gz | sudo tar xz \
 && cd openssl-1.0.2o \
 && sudo ./config shared --prefix=/usr/local/ \
 && sudo make \
 && sudo make install

# python built from source
RUN sudo apt-get install -y wget \
 && wget https://www.python.org/ftp/python/3.9.0/Python-3.9.0.tar.xz \
 && tar -xf Python-3.9.0.tar.xz \
 && cd Python-3.9.0 \
 && ./configure --with-ensurepip=install --with-openssl=/usr/src/openssl-1.0.2o --enable-optimizations \
 && sudo make altinstall

RUN sudo apt-get -y install libmysqlclient-dev

COPY ./new_parking_prediction/requirements.txt ./
RUN sudo python3.9 -m pip install -r requirements.txt

# copy all source code
COPY . .

RUN alias composer="php composer.phar" \
 && sudo composer install \
 && sudo chmod -R 777 storage bootstrap/cache

