import struct

dwords = [-907779687, 1342539355, -1943481129, -242202109, -585186745, -1019284524, 1366497130, 1650496081, 1472276223]
target = b''.join(struct.pack('<i', x) for x in dwords) + struct.pack('<H', 11427) + bytes([25])

key = b"fbec495785a8bcf346b"
a4 = len(key)

def invert_block(y, v7, v8, key: bytes, a4: int, v4_index: int):
    def k(ofs):
        return key[(v4_index + ofs) % a4]
    v12 = (v8 + key[(v4_index - 1) % a4]) & 0xff
    v14 = (v7 + v12) & 0xff
    x0 = ((( (y[0] - v7) & 0xff) ^ k(8)) - v14) & 0xff
    v15 = (x0 + v14 + k(0)) & 0xff
    x1 = ((( (y[1] - x0) & 0xff) ^ k(9)) - v15) & 0xff
    v18 = (v15 + x1 + k(1)) & 0xff
    x2 = ((( (y[2] - x1) & 0xff) ^ k(10)) - v18) & 0xff
    v21 = (v18 + x2 + k(2)) & 0xff
    x3 = ((( (y[3] - x2) & 0xff) ^ k(11)) - v21) & 0xff
    v25 = (v21 + x3 + k(3)) & 0xff
    x4 = ((( (y[4] - x3) & 0xff) ^ k(12)) - v25) & 0xff
    v29 = (v25 + x4 + k(4)) & 0xff
    x5 = ((( (y[5] - x4) & 0xff) ^ k(13)) - v29) & 0xff
    v33 = (v29 + x5 + k(5)) & 0xff
    x6 = ((( (y[6] - x5) & 0xff) ^ k(14)) - v33) & 0xff
    v37 = (v33 + x6 + k(6)) & 0xff
    x7 = ((( (y[7] - x6) & 0xff) ^ k(15)) - v37) & 0xff
    v41 = (v37 + x7 + k(7)) & 0xff
    x8 = ((( (y[8] - x7) & 0xff) ^ k(16)) - v41) & 0xff
    v45 = (v41 + x8 + k(8)) & 0xff
    x9 = ((( (y[9] - x8) & 0xff) ^ k(17)) - v45) & 0xff
    v49 = (v45 + x9 + k(9)) & 0xff
    x10 = ((((y[10] - x9) & 0xff) ^ k(18)) - v49) & 0xff
    v51 = (v49 + x10 + k(10)) & 0xff
    x11 = ((((y[11] - x10) & 0xff) ^ k(19 % a4)) - v51) & 0xff
    v54 = (v51 + k(11)) & 0xff
    t = (y[12] - x11) & 0xff
    u = t ^ k(20 % a4)
    x12 = (u - ((x11 + v54) & 0xff)) & 0xff
    next_v7 = x12
    next_v8 = (x11 + v54) & 0xff
    return [x0,x1,x2,x3,x4,x5,x6,x7,x8,x9,x10,x11,x12], next_v7, next_v8

def invert_all(target_bytes, key=key, a4=a4):
    v7=0
    v8=0
    res=[]
    for blk_idx in range(3):
        v4_index = 1 + 13*blk_idx
        y = list(target_bytes[13*blk_idx:13*(blk_idx+1)])
        xs, v7, v8 = invert_block(y, v7, v8, key, a4, v4_index)
        res.extend(xs)
    return bytes(res)

print(invert_all(target).decode(errors='ignore'))