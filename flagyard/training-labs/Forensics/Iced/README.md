```
                    ▗▄▄▄▖▗▞▀▘▗▞▀▚▖   ▐▌
                      █  ▝▚▄▖▐▛▀▀▘   ▐▌
                      █      ▝▚▄▄▖▗▞▀▜▌
                    ▗▄█▄▖         ▝▚▄▟▌
```

**Category:** Forensics
**Level:** Hard
> The attacker successfully gained access to a target machine. The attacker utilized a legitimate Windows binary to drop malicious code, evading traditional security measures. Additionally, the attacker employed obfuscation techniques to further conceal the malicious activity, making detection and analysis more challenging.

**Flag:** FlagY{d41d8cd98f00b204e9800998ecf8427e}

# SOLUTION

First things first, let's take a look at the challenge file. My first order of business was to `tree` the file and my God did I hit jackpot when I found something fishy called `secret[1].ps1`. So, let's check it out~~

```bash
❯ strings Iced/AppData/Local/Microsoft/Windows/INetCache/IE/XBF3V9IT/secret\[1\].ps1
 (("{4}{58}{17}{20}{53}{47}{42}{69}{66}{48}{44}{7}{30}{62}{50}{31}{56}{14}{59}{70}{43}{51}{11}{23}{39}{64}{25}{6}{15}{5}{46}{28}{54}{37}{45}{61}{13}{40}{16}{9}{63}{22}{29}{27}{41}{33}{1}{32}{0}{71}{12}{34}{67}{10}{36}{68}{55}{3}{19}{49}{18}{65}{24}{21}{2}{35}{57}{52}{8}{38}{60}{26}" -f 'xTCvx','ThmMDBiMjA0ZC','aC','vx+','InvOkE','g(jY','xe64','.En',']','vxbG','xD','v','Cvx','gPSJG','vxtSt','Strin','+C','ExP','vx+CvxYO)))Cvx).R','Cv','ResSION( (','L','C','xystem.CCvx+CvxoCvx+CvxnCvx+Cvxv','p','x+Cv','ChaR]34) )','GNk','CvxJGZ','vxOCvx+Cvx','coCv','vxode','vx+Cv','xO','k4MCvx+CvxDA5','E','I','vx+','89+[ChaR]7','Cvx+Cvxert]::FromBa','Cvx','Cvx+Cv','xi','Cv','vx+Cvxtem.Text','C','OCvx+','v',' ([SysC','xZX0ijC','vx+C','xg([SCvx+C','R','C','sCvx+CvxYWcC','C','.GeCvx+C','(([ChaR]106+[Cha','-','ri','9),[STRInG][','vx','x+CvxdCvx+Cvxing]::UnicC','FnWXtkNDFkCvx+','sCv','e','x','OThlY2Y4NCvx+Cv','3','e','nCvx+','+')).rEPLacE('Cvx',[stRing][CHar]39) |. ( $VERbosePREfEReNCe.tOStRINg()[1,3]+'x'-jOIN'')
```

The challenge description told us that the attacker used obfuscation techniques to make things harder. So, let's deobfuscate it.

![](/assets/images/zig-zag.gif)

```bash
❯ python3 safe_deob.py Iced/AppData/Local/Microsoft/Windows/INetCache/IE/XBF3V9IT/secret\[1\].ps1
InvOkE-ExPResSION( ('iex ([Sys'+'tem.Text.Enco'+'d'+'ing]::Unic'+'ode.Ge'+'tStrin'+'g([S'+'ystem.C'+'o'+'n'+'v'+'ert]::FromBas'+'e64String(jYO'+'JGZs'+'YWc'+'gPSJG'+'bGFnWXtkNDFk'+'O'+'GNk'+'OThmMDBiMjA0Z'+'T'+'k4M'+'DA5OThlY2Y4N'+'DI3'+'ZX0ij'+'YO)))').RepLaCE(([ChaR]106+[ChaR]89+[ChaR]79),[STRInG][ChaR]34) )
```

Deobfuscating the file, I found something that we can work with. It seems that the flag is encoded in base64, to be precise: `jYOJGZsYWcgPSJGbGFnWXtkNDFkOGNkOThmMDBiMjA0ZTk4MDA5OThlY2Y4NDI3ZX0ijYO`

***BUT...*** We can't directly decode it. If we pay attention to the deobfuscated text, we have

```
.RepLaCE(([ChaR]106+[ChaR]89+[ChaR]79),[STRInG][ChaR]34)
```

This means that we have to change the concatenated characters of 106 (`j`), 89 (`Y`), and 79 (`O`) to character 34 (`"`). As such, the string to decode becomes: `"JGZsYWcgPSJGbGFnWXtkNDFkOGNkOThmMDBiMjA0ZTk4MDA5OThlY2Y4NDI3ZX0i"`

```bash
❯ echo "JGZsYWcgPSJGbGFnWXtkNDFkOGNkOThmMDBiMjA0ZTk4MDA5OThlY2Y4NDI3ZX0i" | base64 -d
$flag ="FlagY{d41d8cd98f00b204e9800998ecf8427e}"
```

![](/assets/images/party.gif)