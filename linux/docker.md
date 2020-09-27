# docker
## common command
- docker images : list images
- docker ps = docker container ls: list container
- docker exec = docker run = docker container run -p host_port:container_port -v local_file_path:docker_file_path : run command in container that will be create
- docker container rm 容器名/容器id
- docker stop container_name ：stop container
- docker rm container_name : remove container
- docker container cp file container_name:file = docker cp : copy file
- docker exec -it container_name /bin/bash : enter container
- docker run -it --name=container_name image /bin/bash : run container with bash 覆盖默认命令
- docker contatiner start/restart container_name ： start/restart container
- docker search image_name : search image in Docker Hub
- docker pull image_name = docker image pull user_name/image_name: pull image from Docker Hub
- docker info : docker configuration
- docker attach container : 将标准输入输出错误重定向到容器内。
- docker save -o tar image1 image2 ... : 导出镜像
- docker load -i tar : 导入镜像
- docker export/import -o tar container_name : 导出/导入容器

---
