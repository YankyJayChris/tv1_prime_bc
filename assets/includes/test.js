function solution(S, X, Y) {
  const mytags = {}; 
  const DpCheck = {};

  const distancesFromOrigin = S.split('').reduce((arr, tag, index) => {
    const distance = distanceOfstart(X[index], Y[index]);
    const newtag = { distance, coord: [X[index], Y[index]] };
    if (arr[tag]) {
      if (DpCheck[tag] && !mytags[tag]) {
        mytags[tag] = DpCheck[tag];
      }
      if (distance < arr[tag].distance) {
        arr[tag] = newtag;
      }
      if (distance > arr[tag].distance) {
        mytags[tag] = newtag;
      }

    } else {
      arr[tag] = newtag;
    }
    DpCheck[tag] = newtag;
    return arr;
  }, {});


  let disLimit;
  for (let tag in mytags) {
    if (!disLimit) {
      disLimit = mytags[tag].distance;
    } else if (mytags[tag].distance < disLimit) {
      disLimit = mytags[tag].distance;
    }
  }

  if (disLimit === undefined) return distancesFromOrigin.length;

  const pointsInCircle = Object.values(distancesFromOrigin).filter(record => {
    if (record.distance < disLimit) {
      return true;
    } else {
      return false;
    }
  });
  
  return pointsInCircle.length;
}


function distanceOfstart(x, y) {
  return Math.sqrt(
    Math.pow((x - 0), 2) + Math.pow((y - 0), 2)
  )
}


// task 3

const nNumber = n => (n * (n + 1)) / 2;
function newsolution(S) {
  const myCount = (S.match(/a/g) || []).length;
  if (myCount % 3 !== 0) {
    // Cannot be split evenly among 3 parts:
    return 0;
  }
  if (myCount === 0) {
    return Math.max(nNumber(S.length - 2), 0);
  }
  // Remove the leading characters so that only the middle segment is left:
  let mSection = S;
  for (let i = 0; i < myCount / 3; i++) {
    mSection = mSection.slice(mSection.indexOf('a') + 1);
  }
  // Do the same for the trailing characters:
  for (let i = 0; i < myCount / 3; i++) {
    mSection = mSection.slice(0, mSection.lastIndexOf('a'));
  }
  // Now identify the number of characters leading up to the first `a`
  // and the number of characters from the last `a` to the end:
  const leftSize = mSection.indexOf('a');
  const rightSize = mSection.length - mSection.lastIndexOf('a') - 1;
  return (leftSize + 1) * (rightSize + 1);
};

console.log(solution('babaa')); // 2
console.log(solution('ababa')); // 4
console.log(solution('aba')); // 0
console.log(solution('bbbbb')); // 6
console.log(solution('aaabaaabaaa')); // 4



// task 2in python
class Solution:
  def getLengthOfOptimalCompression(self, s: str, k: int) -> int:
    n = len(s)
    @functools.lru_cache(maxsize=None)
    def dp(i, k):
      if k < 0: return n
      if i + k >= n: return 0
      ans = dp(i + 1, k - 1)
      l = 0
      same = 0
      for j in range(i, n):
        if s[j] == s[i]:
          same += 1
          if same <= 2 or same == 10 or same == 100:
            l += 1
        diff = j - i + 1 - same
        if diff < 0: break
        ans = min(ans, l + dp(j + 1, k - diff))
      return ans
    return dp(0, k)
