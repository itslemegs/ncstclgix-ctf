```
  ▗▖ ▄▄▄  ▗▞▀▘█  ▄ ▗▞▀▚▖   ▐▌     ▗▄▄▖▗▞▀▚▖▗▞▀▘ ▄▄▄ ▗▞▀▚▖   ■   ▄▄▄ 
  ▐▌█   █ ▝▚▄▖█▄▀  ▐▛▀▀▘   ▐▌    ▐▌   ▐▛▀▀▘▝▚▄▖█    ▐▛▀▀▘▗▄▟▙▄▖▀▄▄  
  ▐▌▀▄▄▄▀     █ ▀▄ ▝▚▄▄▖▗▞▀▜▌     ▝▀▚▖▝▚▄▄▖    █    ▝▚▄▄▖  ▐▌  ▄▄▄▀ 
  ▐▙▄▄▖       █  █      ▝▚▄▟▌    ▗▄▄▞▘                     ▐▌       
                                                           ▐▌       
```

**Category:** Forensics
**Level:** Medium
> A financial institution was breached, and attackers accessed sensitive records on a BitLocker-encrypted drive. They decrypted a local admin password stored in a hidden configuration file to unlock the drive. Your task is to recover the password, unlock the drive, and retrieve the file containing the flag.

**Flag:** FlagY{a8b4c9a8d9e0f5b9a77b89a7b1a5b9f8}

# SOLUTION

Following the information given in the challenge description, I checked out the files and found something interesting in `chall > triage image > E > Windows > SYSVOL > domain > Policies > {F6E536A8-76F2-4111-A113-C19D882367AF} > Machine > Preferences > Groups > Groups.xml`.

```bash
❯ strings chall/triage\ image/E/Windows/SYSVOL/domain/Policies/\{F6E536A8-76F2-4111-A113-C19D882367AF\}/Machine/Preferences/Groups/Groups.xml
<?xml version="1.0" encoding="utf-8"?>
<Groups clsid="{3125E937-EB16-4b4c-9934-544FC6D24D26}"><User clsid="{DF5F1855-51E5-4d24-8B1A-D9BDE98BA1D1}" name="SM" image="0" changed="2024-09-06 12:12:55" uid="{E4073160-0C87-4FCE-9ED1-0C890558E45E}"><Properties action="C" fullName="mr.shosho" description="" cpassword="Xpk4DyKDjYQQ/EUHClHlP+m6AKRQL5cC+JABNiw5C9aUMrmXIH2Mkky4a8tKUxIq" changeLogon="1" noChange="0" neverExpires="0" acctDisabled="0" userName="SM"/></User>
</Groups>
```

We found this encrypted `cpassword` stored in a Group Policy Preferences configuration file. Since I'm unfamiliar with Windows, I looked up GPP on the internet. Turns out, GPP is a Windows feature introduced around Windows Server 2008 that lets administrators deploy settings to machines in a domain. These are implemented as XML files stored in the domain's SYSVOL share, so every domain-joined computer can read them. The idea was to “encrypt” the password, but Microsoft hard-coded the encryption key in the specification, the same key for every Windows domain on Earth. Once that key was published (2012 / MS-GPPREF spec), anyone who could read SYSVOL could decrypt all GPP passwords.

So, I found that the 32-byte AES key is [[1](https://learn.microsoft.com/en-us/openspecs/windows_protocols/ms-gppref/2c15cbf0-f086-4c74-8b70-1f2fa45dd4be)]:

```
        4e 99 06 e8       fc b6 6c c9       fa f4 93 10       62 0f fe e8
        f4 96 e8 06       cc 05 79 90       20 9b 09 a4       33 b6 6c 1b
```

From there, I decrypted the `cpassword` using solve.py and got: `Passw0rd123!@Meme`.


```bash
┌──(root㉿kali)-[/mnt]
└─# mkdir -p /tmp/ewf_bit /mnt/bitlocker_unlocked /mnt/bitlocker_mounted

┌──(root㉿kali)-[/mnt]
└─# ewfmount share/chall/bitlocker\ drive/bitlocker_drive.E01 /tmp/ewf_bit
ewfmount 20140816

┌──(root㉿kali)-[/mnt]
└─# mmls /tmp/ewf_bit/ewf1
DOS Partition Table
Offset Sector: 0
Units are in 512-byte sectors

      Slot      Start        End          Length       Description
000:  Meta      0000000000   0000000000   0000000001   Primary Table (#0)
001:  -------   0000000000   0000000127   0000000128   Unallocated
002:  000:000   0000000128   0000139391   0000139264   NTFS / exFAT (0x07)
003:  -------   0000139392   0000145407   0000006016   Unallocated

┌──(root㉿kali)-[/mnt]
└─# OFFSET=$((128 * 512))

┌──(root㉿kali)-[/mnt]
└─# LOOP=$(losetup --find --show -o $OFFSET /tmp/ewf_bit/ewf1)

┌──(root㉿kali)-[/mnt]
└─# bdeinfo $LOOP
bdeinfo 20240502

Volume is locked and a password is needed to unlock it.

Password: 

BitLocker Drive Encryption information:
        Volume identifier               : 39715540-32c3-44cd-9caf-0ed0a7edd0ff
        Size                            : 68 MiB (71303168 bytes)
        Encryption method               : AES-CBC 128-bit with Diffuser
        Creation time                   : Sep 06, 2024 12:59:17.465689200 UTC
        Description                     : WIN-688U8N3F5QG New Volume 9/6/2024
        Number of key protectors        : 2

Key protector 0:
        Identifier                      : 0e10e4e3-fed1-4bb7-8446-9440c183ec71
        Type                            : Password

Key protector 1:
        Identifier                      : 889fbb1e-6e5a-44a7-9039-1976b222a458
        Type                            : Recovery password

┌──(root㉿kali)-[/mnt]
└─# bdemount -p "$PASSWORD" $LOOP /mnt/bitlocker_unlocked
bdemount 20240502

┌──(root㉿kali)-[/mnt]
└─# LOOP2=$(losetup --find --show /mnt/bitlocker_unlocked/bde1)

┌──(root㉿kali)-[/mnt]
└─# mount -o ro $LOOP2 /mnt/bitlocker_mounted

┌──(root㉿kali)-[/mnt]
└─# ls -la /mnt/bitlocker_mounted | sed -n '1,200p' || true
total 13
drwxrwxrwx 1 root root 4096 Sep  6  2024 .
drwxr-xr-x 6 root root 4096 Oct 17 23:30 ..
drwxrwxrwx 1 root root 4096 Sep  6  2024 System Volume Information
-rwxrwxrwx 2 root root   39 Sep  6  2024 the flag.txt

┌──(root㉿kali)-[/mnt]
└─# cat "/mnt/bitlocker_mounted/the flag.txt"
FlagY{a8b4c9a8d9e0f5b9a77b89a7b1a5b9f8}
```

![](/assets/images/so-suck-it.gif)