
/*
* Licensed to the Apache Software Foundation (ASF) under one
* or more contributor license agreements.  See the NOTICE file
* distributed with this work for additional information
* regarding copyright ownership.  The ASF licenses this file
* to you under the Apache License, Version 2.0 (the
* "License"); you may not use this file except in compliance
* with the License.  You may obtain a copy of the License at
*
*   http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing,
* software distributed under the License is distributed on an
* "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
* KIND, either express or implied.  See the License for the
* specific language governing permissions and limitations
* under the License.
*/


/**
 * AUTO-GENERATED FILE. DO NOT MODIFY.
 */

/*
* Licensed to the Apache Software Foundation (ASF) under one
* or more contributor license agreements.  See the NOTICE file
* distributed with this work for additional information
* regarding copyright ownership.  The ASF licenses this file
* to you under the Apache License, Version 2.0 (the
* "License"); you may not use this file except in compliance
* with the License.  You may obtain a copy of the License at
*
*   http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing,
* software distributed under the License is distributed on an
* "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
* KIND, either express or implied.  See the License for the
* specific language governing permissions and limitations
* under the License.
*/
import { each, isString } from 'zrender/lib/core/util';
/**
 * Note that it is too complicated to support 3d stack by value
 * (have to create two-dimension inverted index), so in 3d case
 * we just support that stacked by index.
 *
 * @param seriesModel
 * @param dimensionInfoList The same as the input of <module:echarts/data/List>.
 *        The input dimensionInfoList will be modified.
 * @param opt
 * @param opt.stackedCoordDimension Specify a coord dimension if needed.
 * @param opt.byIndex=false
 * @return calculationInfo
 * {
 *     stackedDimension: string
 *     stackedByDimension: string
 *     isStackedByIndex: boolean
 *     stackedOverDimension: string
 *     stackResultDimension: string
 * }
 */

export function enableDataStack(seriesModel, dimensionInfoList, opt) {
  opt = opt || {};
  var byIndex = opt.byIndex;
  var stackedCoordDimension = opt.stackedCoordDimension; // Compatibal: when `stack` is set as '', do not stack.

  var mayStack = !!(seriesModel && seriesModel.get('stack'));
  var stackedByDimInfo;
  var stackedDimInfo;
  var stackResultDimension;
  var stackedOverDimension;
  each(dimensionInfoList, function (dimensionInfo, index) {
    if (isString(dimensionInfo)) {
      dimensionInfoList[index] = dimensionInfo = {
        name: dimensionInfo
      };
    }

    if (mayStack && !dimensionInfo.isExtraCoord) {
      // Find the first ordinal dimension as the stackedByDimInfo.
      if (!byIndex && !stackedByDimInfo && dimensionInfo.ordinalMeta) {
        stackedByDimInfo = dimensionInfo;
      } // Find the first stackable dimension as the stackedDimInfo.


      if (!stackedDimInfo && dimensionInfo.type !== 'ordinal' && dimensionInfo.type !== 'time' && (!stackedCoordDimension || stackedCoordDimension === dimensionInfo.coordDim)) {
        stackedDimInfo = dimensionInfo;
      }
    }
  });

  if (stackedDimInfo && !byIndex && !stackedByDimInfo) {
    // Compatible with previous design, value axis (time axis) only stack by index.
    // It may make sense if the user provides elaborately constructed data.
    byIndex = true;
  } // Add stack dimension, they can be both calculated by coordinate system in `unionExtent`.
  // That put stack logic in List is for using conveniently in echarts extensions, but it
  // might not be a good way.


  if (stackedDimInfo) {
    // Use a weird name that not duplicated with other names.
    stackResultDimension = '__\0ecstackresult';
    stackedOverDimension = '__\0ecstackedover'; // Create inverted index to fast query index by value.

    if (stackedByDimInfo) {
      stackedByDimInfo.createInvertedIndices = true;
    }

    var stackedDimCoordDim_1 = stackedDimInfo.coordDim;
    var stackedDimType = stackedDimInfo.type;
    var stackedDimCoordIndex_1 = 0;
    each(dimensionInfoList, function (dimensionInfo) {
      if (dimensionInfo.coordDim === stackedDimCoordDim_1) {
        stackedDimCoordIndex_1++;
      }
    });
    dimensionInfoList.push({
      name: stackResultDimension,
      coordDim: stackedDimCoordDim_1,
      coordDimIndex: stackedDimCoordIndex_1,
      type: stackedDimType,
      isExtraCoord: true,
      isCalculationCoord: true
    });
    stackedDimCoordIndex_1++;
    dimensionInfoList.push({
      name: stackedOverDimension,
      // This dimension contains stack base (generally, 0), so do not set it as
      // `stackedDimCoordDim` to avoid extent calculation, consider log scale.
      coordDim: stackedOverDimension,
      coordDimIndex: stackedDimCoordIndex_1,
      type: stackedDimType,
      isExtraCoord: true,
      isCalculationCoord: true
    });
  }

  return {
    stackedDimension: stackedDimInfo && stackedDimInfo.name,
    stackedByDimension: stackedByDimInfo && stackedByDimInfo.name,
    isStackedByIndex: byIndex,
    stackedOverDimension: stackedOverDimension,
    stackResultDimension: stackResultDimension
  };
}
export function isDimensionStacked(data, stackedDim
/*, stackedByDim*/
) {
  // Each single series only maps to one pair of axis. So we do not need to
  // check stackByDim, whatever stacked by a dimension or stacked by index.
  return !!stackedDim && stackedDim === data.getCalculationInfo('stackedDimension'); // && (
  //     stackedByDim != null
  //         ? stackedByDim === data.getCalculationInfo('stackedByDimension')
  //         : data.getCalculationInfo('isStackedByIndex')
  // );
}
export function getStackedDimension(data, targetDim) {
  return isDimensionStacked(data, targetDim) ? data.getCalculationInfo('stackResultDimension') : targetDim;
}