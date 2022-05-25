# cmdpb

A simple private command-line pastebin that uses HTTP basic authentication and
MySQL.

## Get started

### Server-side:
  Copy `cmdpb.php` and `secrets.php` to your server (say to `example.com/`).
  Edit `secrets.php` to provide login credentials and database configurations.

### Client-side:
You can communicate with the pastebin using `curl`
or using the shell script `cmdpb` (see below).
There are two options to authenticate using `curl`:

* `curl -n`: which reads login credentials from your `~/.netrc`.
  You can enter your login credentials to `~/.netrc` like this:
  ```
  machine example.com login USERNAME password PASSWORD
  ```
  
* `curl -u USERNAME:PASSWORD`: by providing login credentials in each call.


## Usage
Posting a new paste

* from a `FILE`:
  ```
  curl -n -F "c=@FILE" https://example.com/cmdpb.php
  ```
  
* From `stdin`:
  ```
  echo Hello world | curl -n -F "c=<-" https://example.com/cmdpb.php
  ```
  
* from a string:
  ```
  curl -n -F "c=Hello world" https://example.com/cmdpb.php
  ```

Getting all pastes:
```
curl -n https://example.com/cmdpb.php
```

Getting the paste with `id=ID`:
```
curl -n "https://example.com/cmdpb.php?id=ID"
```

Deleting the paste with `id=ID`:
```
curl -n -X DELETE "https://example.com/cmdpb.php?id=ID"
```

Updating the paste with `id=ID`:
```
curl -n -F "c=Hello world" "https://example.com/cmdpb.php?id=ID"
curl -n -F "c=@file" "https://example.com/cmdpb.php?id=ID"
echo Hello world | curl -n -F "c=<-" "https://example.com/cmdpb.php?id=ID"
```

## Shell script
A shell script `cmdpb` is also provided.
To start using it,
download it,
make it executable
and edit it to make the variable `URL` points to your `cmdpb.php`.

Examples of use:
```
cmdpb                         # post from what you write
cmdpb file                    # post from file
echo hello world | cmdpb      # post from stdin
cmdpb -a                      # show all pastes
cmdpb -s ID                   # show paste with id=ID
cmdpb -d ID                   # delete paste with id=ID
cmdpb -u ID                   # update paste with id=ID
```

## Security
By default, the pastebin works only with secure connections (`https`).
If you wish to run it locally or on a completely secure network,
you can comment out the line:
```
if (!isSecure()){echo "please use https\n"; exit; }
```
