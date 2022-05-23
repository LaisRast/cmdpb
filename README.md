# cmdpb

A simple private command-line pastebin that uses HTTP basic authentication and
MySQL.

## Get started

### Server-side:
  Copy `index.php` and `secrets.php` to your server (say `example.com`).
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
Posting a new pastebin

* from a `FILE`:
  ```
  curl -n -F "c=@FILE" https://example.com/
  ```
  
* From `stdin`:
  ```
  echo Hello world | curl -n -F "c=<-" https://example.com/
  ```
  
* from a string:
  ```
  curl -n -F "c=Hello world" https://example.com/
  ```

Getting all pastebins:
```
curl -n https://example.com/
```

Getting the pastebin with `id=ID`:
```
curl -n "https://example.com/?id=ID"
```

Deleting the pastebin with `id=ID`:
```
curl -n -X DELETE "https://example.com/?id=ID"
```

Updating the pastebin with `id=ID`:
```
curl -n -F "c=Hello world" "https://example.com/?id=ID"
curl -n -F "c=@file" "https://example.com/id=ID"
echo Hello world | curl -n -F "c=<-" "https://example.com/?id=ID"
```

## Shell script
A shell script `cmdpd` is also provided.
To start using it,
download it,
make it executable
and edit it to make the variable `URL` points to your server.

Examples of use:
```
cmdpd                         # paste from what you write
cmdpd file                    # paste from file
echo hello world | cmdpd      # paste from stdin
cmdpd -a                      # show all pastebins
cmdpd -s ID                   # show pastebin with id=ID
cmdpd -d ID                   # delete pastebin with id=ID
cmdpd -u ID                   # update pastebin with id=ID
```

## Security
By default, the pastebin works only with secure connections (`https`).
If you wish to run it locally or on a completely secure network,
you can comment out the line:
```
if (!isSecure()){echo "please use https\n"; exit; }
```
