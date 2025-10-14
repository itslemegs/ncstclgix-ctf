```
                    ▗▄▄▄▖ ▗▄▄▖ ▗▄▄▖ ▗▄▄▖ 
                    ▐▌ ▐▌ ▐▌ ▐▌▐▌ ▐▌▐▌ ▐▌
                    ▐▌ ▐▌ ▐▛▀▚▖▐▛▀▚▖▐▛▀▚▖
                    ▐▙▄▟▙▖▐▌ ▐▌▐▌ ▐▌▐▌ ▐▌
```

**Category:** Forensics
**Level:** Easy
> Scan it, get it.

**Flag:** FlagY{Congrats_u_got_ittt}

# SOLUTION

The challenge file is a GIF. Upon inspection, we can see that it's a collection of QR Codes. The task is as simple as scanning the codes... or is it?

Obviously, there are a lot of ways to get the frames. But I opted for the tools that I already had because I'm just too lazy.

![relax-lazy](/assets/images/relax-lazy.gif)

Basically, I used `ffmpeg` to extract the frames in the GIF. Notice how I removed the `!` because ***WTF?!*** Anyway... watching the GIF earlier, I noticed that some QR codes are displayed longer than the others—i.e., there are several frames displaying the same codes. Well... I mean... I could remove the duplicates first... But then again... So, I just went ahead and read me some QR codes. But for your sake, I remove the duplicates *(how kind am i?)*

```bash
❯ ffmpeg -i QRRR.gif frames/frame_%03d.png
❯ zbarimg -Sdisable -Sqrcode.enable --raw -q frames/*.png
SyntL{q6pnbcxfqcbnfxqEEE!}
SyntL{qbnfxqcbnfxqnfxqcbnf_nbfqwfnvwqbnvfwqbv_nfffnff!}
SyntL{ncfqx7c6jbxf_qbfws6_qbvfwqbvswf!}
SyntL{nfxqcbnxq_66765_fbqvs6666!}
SyntL{nbfcxqcbfnxq_fvbqswfqsfbqs_fbbbbrrrrr___rvswbnvfw!}
SyntL{qstqtsqtvewre_qvfqcs_qsqs!}
SyntL{fxwqxwf_qfbq_qfswfqsf!}
SyntL{vvfqsfq_xqfwxfq_qwfqf!}
SyntL{wuxqysfqyn_fqcbfqcqf_5678765qqqq!}
SyntL{Pbatengf_h_tbg_vggg}
SyntL{ncfxbqcnxfq_vqfnwqs56!}
SyntL{qfstugtstsqfr_ss!}
SyntL{abg_gur_synt_jnyynu!}
SyntL{fnyqnbfxqfncbxqnnbfcxq!}
SyntL{kxqsxbfcxsfb_fqfqfqfqfqqfqf!}
SyntL{abg_gur_synnnnnnnnnnnnttttt!}
SyntL{lnfcqxbcfnxqcbnxqf6!}
SyntL{qbnfcbxqfn7!}
SyntL{bqfc2tq!}
SyntL{bqfc2tqfqsfq!}
```

So, there's that. What a weird ass flag. What a long list... Well, anyway, I had my suspicions but I went ahead and submitted them one by one anyway. Who knows, right? Surprise, surprise, none of them passed.

![denial](/assets/images/denial.gif)

Anyway, that's when I checked the Discord channel and man oh man, the only resources I could find was in arabic. So, Google Translate to the rescue. Long story short, the interesting tidbit was to decode it before submission. This triggered me because I *knew* that the flag format's `FlagY{...}`; but the author was `SAFCSP` so... I mean... it's possible, right?

Well, let's get to decoding. I started with the easiest aka the Caesar Cipher. I counted and anyway it used a shift of 13, i.e., a ROT13.

```bash
❯ zbarimg -Sdisable -Sqrcode.enable --raw -q frames/*.png \
  | tr 'A-Za-z' 'N-ZA-Mn-za-m'
FlagY{d6caopksdpoaskdRRR!}
FlagY{doaskdpoaskdaskdpoas_aosdjsaijdoaisjdoi_asssass!}
FlagY{apsdk7p6woks_dosjf6_doisjdoifjs!}
FlagY{askdpoakd_66765_sodif6666!}
FlagY{aospkdposakd_siodfjsdfsodf_sooooeeeee___eifjoaisj!}
FlagY{dfgdgfdgirjer_disdpf_dfdf!}
FlagY{skjdkjs_dsod_dsfjsdfs!}
FlagY{iisdfsd_kdsjksd_djsds!}
FlagY{jhkdlfsdla_sdposdpds_5678765dddd!}
FlagY{Congrats_u_got_ittt}
FlagY{apskodpaksd_idsajdf56!}
FlagY{dsfghtgfgfdse_ff!}
FlagY{not_the_flag_wallah!}
FlagY{saldaoskdsapokdaaospkd!}
FlagY{xkdfkospkfso_sdsdsdsdsddsds!}
FlagY{not_the_flaaaaaaaaaaaaggggg!}
FlagY{yaspdkopsakdpoakds6!}
FlagY{doaspokdsa7!}
FlagY{odsp2gd!}
FlagY{odsp2gdsdfsd!}
```

![](/assets/images/ive-heard-it-both-ways.gif)